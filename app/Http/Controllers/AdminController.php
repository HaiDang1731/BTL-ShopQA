<?php
namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Facades\Image as ImageFacade;

use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {
        return view("admin.index");
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view("admin.brands", compact('brands'));
    }

    public function add_brand()
    {
        return view("admin.brand-add");
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateBrandThumbnailImage($image, $file_name);
            $brand->image = $file_name;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Đã thêm thành công!');
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand_edit', compact('brand'));
    }

    public function brand_update($id, Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = Brand::find($id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if (File::exists(public_path("uploads/brands") . '/' . $brand->image)) {
                File::delete(public_path("uploads/brands") . '/' . $brand->image);
            }
            $img = $request->file('image');
            $file_extension = $img->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateBrandThumbnailImage($img, $file_name);
            $brand->image = $file_name;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Đã cập nhật thành công!');
    }

    public function GenerateBrandThumbnailImage($image, $imageName)
    {
        try {
            $destinationPath = public_path("uploads/brands");
            $img = Image::read($image->path());
            $img->resize(124, 124, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $imageName);
        } catch (\Exception $e) {
            return back()->withErrors(['image' => 'Có lỗi xảy ra khi xử lý hình ảnh.']);
        }
    }

    // xóa brand
    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        if (File::exists(public_path("uploads/brand") . '/' . $brand->image)) {
            File::delete(public_path("uploads/brand") . '/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Đã xóa brand thành công!');
    }


    //category
    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view("admin.categories", compact('categories'));
    }

    public function category_add()
    {
        return view("admin.category-add");
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateCategoryThumbailImage($image, $file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Đã thêm thành công!');
    }
    public function GenerateCategoryThumbailImage($image, $imageName)
    {
        try {
            $destinationPath = public_path("uploads/categories");
            $img = Image::read($image->path());
            $img->resize(124, 124, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . '/' . $imageName);
        } catch (\Exception $e) {
            return back()->withErrors(['image' => 'Có lỗi xảy ra khi xử lý hình ảnh.']);
        }
    }
    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update($id, Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $category = Category::find($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if (File::exists(public_path("uploads/categories") . '/' . $category->image)) {
                File::delete(public_path("uploads/categories") . '/' . $category->image);
            }
            $img = $request->file('image');
            $file_extension = $img->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateCategoryThumbailImage($img, $file_name);
            $category->image = $file_name;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Đã cập nhật thành công!');
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path("uploads/categories") . '/' . $category->image)) {
            File::delete(public_path("uploads/categories") . '/' . $category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Đã xóa brand thành công!');
    }


    //products
    public function products()
    {
        $products = Product::OrderBy('created_at', 'DESC')->paginate(10);
        return view("admin.products", compact('products'));
    }


    public function add_product()
    {
        $categories = Category::Select('id', 'name')->orderBy('name')->get();
        $brands = Brand::Select('id', 'name')->orderBy('name')->get();
        return view("admin.product-add", compact('categories', 'brands'));
    }

    public function product_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'category_id' => 'required',
            'brand_id' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
                File::delete(public_path('uploads/products') . '/' . $product->image);
            }
            if (File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image)) {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
            }

            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            $oldGImages = explode(",", $product->images);
            foreach ($oldGImages as $gimage) {
                if (File::exists(public_path('uploads/products') . '/' . trim($gimage))) {
                    File::delete(public_path('uploads/products') . '/' . trim($gimage));
                }
                if (File::exists(public_path('uploads/products/thumbails') . '/' . trim($gimage))) {
                    File::delete(public_path('uploads/products/thumbails') . '/' . trim($gimage));
                }
            }
            $allowedfileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $check = in_array($gextension, $allowedfileExtension);
                if ($check) {
                    $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateThumbnailImage($file, $gfilename);
                    array_push($gallery_arr, $gfilename);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images = $gallery_images;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Sản phẩm đã được thêm thành công !');
    }

    public function GenerateThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/products/');
        $thumbnailPath = public_path('uploads/products/thumbnails/');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        if (!File::exists($thumbnailPath)) {
            File::makeDirectory($thumbnailPath, 0755, true);
        }

        // Lưu ảnh gốc
        $image->move($destinationPath, $imageName);

        // Tạo và lưu ảnh thumbnail
        $thumbnailImage = Image::read($destinationPath . $imageName);
        $thumbnailImage->resize(150, 150);
        $thumbnailImage->save($thumbnailPath . $imageName);
    }


    public function edit_product($id)
    {
        $product = Product::find($id);
        $categories = Category::Select('id', 'name')->orderBy('name')->get();
        $brands = Brand::Select('id', 'name')->orderBy('name')->get();
        return view('admin.product-edit', compact('product', 'categories', 'brands'));
    }

    //cập nhât product
    // public function update_product(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required',
    //         'slug' => 'required|unique:products,slug,' . $request->id,
    //         'category_id' => 'required',
    //         'brand_id' => 'required',
    //         'short_description' => 'required',
    //         'description' => 'required',
    //         'regular_price' => 'required',
    //         'sale_price' => 'required',
    //         'SKU' => 'required',
    //         'stock_status' => 'required',
    //         'featured' => 'required',
    //         'quantity' => 'required',
    //         'image' => 'required|mimes:png,jpg,jpeg|max:2048'
    //     ]);

    //     $product = Product::find($request->id);
    //     $product->name = $request->name;
    //     $product->slug = Str::slug($request->name);
    //     $product->short_description = $request->short_description;
    //     $product->description = $request->description;
    //     $product->regular_price = $request->regular_price;
    //     $product->sale_price = $request->sale_price;
    //     $product->SKU = $request->SKU;
    //     $product->stock_status = $request->stock_status;
    //     $product->featured = $request->featured;
    //     $product->quantity = $request->quantity;
    //     $current_timestamp = Carbon::now()->timestamp;

    //     if ($request->hasFile('image')) {
    //         if ($request->hasFile('image')) {
    //             if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
    //                 File::delete(public_path('uploads/products') . '/' . $product->image);
    //             }
    //             if (File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image)) {
    //                 File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
    //             }

    //             $image = $request->file('image');
    //             $imageName = $current_timestamp . '.' . $image->extension();
    //             $this->GenerateThumbnailImage($image, $imageName);
    //             $product->image = $imageName;
    //         }
    //     }

    //     $gallery_arr = array();
    //     $gallery_images = "";
    //     $counter = 1;

    //     if ($request->hasFile('images')) {
    //         $allowedfileExtension = ['jpg', 'png', 'jpeg'];
    //         $files = $request->file('images');
    //         foreach ($files as $file) {
    //             $gextension = $file->getClientOriginalExtension();
    //             $check = in_array($gextension, $allowedfileExtension);
    //             if ($check) {
    //                 $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
    //                 $this->GenerateThumbnailImage($file, $gfilename);
    //                 array_push($gallery_arr, $gfilename);
    //                 $counter = $counter + 1;
    //             }
    //         }
    //         $gallery_images = implode(', ', $gallery_arr);
    //     }
    //     $product->images = $gallery_images;
    //     $product->save();
    //     return redirect()->route('admin.products')->with('status', 'Sản phẩm đã được cập nhật thành công !');
    // }
    public function update_product(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $request->id,
            'category_id' => 'required',
            'brand_id' => 'required',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048'  // Cho phép ảnh không bắt buộc
        ]);

        // Tìm sản phẩm bằng ID
        $product = Product::find($request->id);

        // Cập nhật các trường thông tin sản phẩm
        $product->update($request->only([
            'name',
            'slug',
            'category_id',
            'brand_id',
            'short_description',
            'description',
            'regular_price',
            'sale_price',
            'SKU',
            'stock_status',
            'featured',
            'quantity'
        ]));

        $current_timestamp = Carbon::now()->timestamp;

        // Nếu có ảnh mới được tải lên
        if ($request->hasFile('image')) {
            $this->storeOrUpdateProductImage($product, $request, $current_timestamp);
        }

        // Cập nhật ảnh gallery nếu có
        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if ($request->hasFile('images')) {
            $allowedfileExtension = ['jpg', 'png', 'jpeg'];
            $files = $request->file('images');
            foreach ($files as $file) {
                $gextension = $file->getClientOriginalExtension();
                $check = in_array($gextension, $allowedfileExtension);
                if ($check) {
                    $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateThumbnailImage($file, $gfilename);
                    array_push($gallery_arr, $gfilename);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
        }

        $product->images = $gallery_images;

        // Lưu lại thông tin sản phẩm
        $product->save();

        return redirect()->route('admin.products')->with('status', 'Sản phẩm đã được cập nhật thành công!');
    }

    //xóa product
    public function delete_product($id)
    {
        $product = Product::find($id);
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'sản phẩm đâ được xóa thành công !');
    }


}
