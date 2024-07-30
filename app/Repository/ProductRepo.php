<?php

namespace App\Repository;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Review;
use App\Models\CartItem;
use App\Models\Complaint;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;

class ProductRepo implements IProductRepo
{
    public function FetchProducts($id)
    {
        $products = Product::where('school_id', $id)
                            ->reorder('created_at', 'desc')
                            ->get();
        // Iterating through each product and calculate the average rating
        foreach ($products as $product) {
            $ratings = Review::where('product_id', $product->id)->pluck('rating')->toArray();
            $averageRating = (count($ratings) > 0) ? array_sum($ratings) / count($ratings) : 0;
            // Adding the average rating to each product's object
            $product->avg_rating = $averageRating;
        }
        return $products;
    }
    public function FetchCategories($id)
    {
        $categories = Category::where('school_id', $id)
                                ->get();
        $subcategories = Subcategory::where('school_id', $id)
                                    ->get();
        return [$categories, $subcategories];
    }
    public function FetchSearchResults(Request $request)
    {
        //$exactResult = Product::where('name', $searchTerm)->get();

        $searchTerm = $request->input('searchTerm');
        $productResults = Product::where('name', 'LIKE', '%' . $searchTerm . '%')->get();
        $eventResults = Event::where('event_info', 'LIKE', '%' . $searchTerm . '%')->get();
        if (($eventResults->isEmpty()) && ($productResults->isEmpty())) {
            return [null, null];
        } else {
            return [
                $productResults,
                $eventResults,
            ];
        }
    }
    public function GetAvgRating($id)
    {
        $ratings = Review::where('product_id', $id)->pluck('rating')->toArray();
        if (count($ratings) > 0) {
            $averageRating = array_sum($ratings) / count($ratings);
            return $averageRating;
        } else {
            return 0;
        }
    }

    public function GetFeaturedProducts($id)
    {
        // Select three products with reviews and ratings
        $featuredProducts = Product::where('school_id', $id)
            ->join('reviews', 'products.id', '=', 'reviews.product_id')
            ->select('products.*', 'reviews.comment', 'reviews.rating')
            ->whereNotNull('reviews.comment')
            ->whereNotNull('reviews.rating')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // Check if there are less than three products with reviews and ratings
        if ($featuredProducts->count() < 3) {
            // Select newly added products to fill the gap
            $newProducts = Product::orderBy('created_at', 'DESC')
                ->whereNotIn('id', $featuredProducts->pluck('product_id'))
                ->limit(3 - $featuredProducts->count())
                ->get();
            // Merge the collections to get the final result
            $featuredProducts = $featuredProducts->merge($newProducts);
        }

        foreach ($featuredProducts as $product) {
            $ratings = Review::where('product_id', $product->id)->pluck('rating')->toArray();
            $averageRating = (count($ratings) > 0) ? array_sum($ratings) / count($ratings) : 0;
            // Adding the average rating to each product's object
            $product->avg_rating = $averageRating;
        }

        return $featuredProducts;
    }
    public function GetProduct($id)
    {
        $product = Product::where('id', $id)->first();
        $reviews = Review::where('product_id', $id)->get();

        if ($product) {
            $ratings = Review::where('product_id', $product->id)->pluck('rating')->toArray();
            $averageRating = (count($ratings) > 0) ? array_sum($ratings) / count($ratings) : 0;
            $product->avg_rating = $averageRating;
            return [$product, $reviews];
        } else {
            return false;
        }
    }
    public function FetchRelatedProducts($id)
    {
        $product = Product::where('id', $id)->first();

        if ($product) {
            $category_id = $product->category_id;

            $relatedProducts = Product::where('category_id', $category_id)
                ->where('id', '!=', $id)
                ->take(3)
                ->get();

            if ($relatedProducts->count() < 3) {
                $additionalProducts = Product::where('id', '!=', $id)
                    ->take(3 - $relatedProducts->count())
                    ->get();

                $relatedProducts = $relatedProducts->merge($additionalProducts);
            }


            foreach ($relatedProducts as $relatedProduct) { // Change variable name to $relatedProduct
                $ratings = Review::where('product_id', $relatedProduct->id)->pluck('rating')->toArray();
                $averageRating = (count($ratings) > 0) ? array_sum($ratings) / count($ratings) : 0;

                // Adding the average rating to each product's object
                $relatedProduct->avg_rating = $averageRating;
            }
            return $relatedProducts;
        } else {
            return Product::take(3)->get()->toArray();
        }
    }
    public function AddProduct($validatedData)
    {
        $product = Product::create($validatedData);
        if ($product) {
            return true;
        } else {
            return false;
        }
    }
    public function DeleteProduct($id)
    {
        $product = Product::where('id', $id)->first();
        if ($product) {
            $paths = json_decode($product->images);
            foreach ($paths as $path) {
                Storage::disk('s3')->delete($path);
            }
            // Delete related reviews
            Review::where('product_id', $id)->delete();

            // Delete related cart items
            CartItem::where('product_id', $id)->delete();

            // Delete related complaints
            Complaint::where('product_id', $id)->delete();
            $product->delete();
            return true;
        } else {
            return false;
        }
    }
    public function UpdateProduct(Request $request)
    {
        $id = $request->input('id');
        $product = Product::where('id', $id)->first();
        if ($product) {
            $product->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'stock' => $request->input('stock'),
                'size' => $request->input('size'),
                'color' => $request->input('color'),
                'price' => $request->input('price'),
                'discount' => $request->input('discount'),
                'discounted_price' => $request->input('discounted_price'),
                'images' => $request->input('images'),
                'category_id' => $request->input('category_id'),
                'subcategory_id' => $request->input('subcategory_id'),
            ]);
            return true;
        } else {
            return false;
        }
    }
    public function AddCategory($validatedData)
    {
        $category = Category::create($validatedData);
        if ($category) {
            return true;
        } else {
            return false;
        }
    }
    public function AddSubCategory($validatedData)
    {
        $subcategory = Subcategory::create($validatedData);
        if ($subcategory) {
            return true;
        } else {
            return false;
        }
    }
    public function StockUpdate($id, $stock)
    {
        $product = Product::find($id);

        if ($product) {
            $product->stock = $stock;
            $product->save();
        }
        return $product ? true : false;
    }
    public function AddProductImgs(Request $request)
    {
        $paths = [];

        foreach ($request->file('imgs') as $file) {
            // 'public/products' - directory
            $path = $file->store(
                'public/products',
                's3'
            );

            // visibility of the file - public
            Storage::disk('s3')->setVisibility($path, 'public');

            // Generating a public URL for the file
            //$publicUrl = Storage::disk('s3')->url($path);

            // Add the path to the array
            $paths[] = $path;
            //$publicUrls[] = $publicUrl;
        }
        return $paths;
    }
    public function UpdateProductImgs(Request $request)
    {
        $paths = [];
        $product = Product::find($request->input('id'));

        foreach ($request->file('imgs') as $file) {
            $path = $file->store(
                'public/products',
                's3'
            );
            Storage::disk('s3')->setVisibility($path, 'public');
            $paths[] = $path;
        }
        if ($product) {
            $decodedImages = json_decode($product->images, true);
            $mergedImages = array_merge($decodedImages, $paths);
            $product->images = ($mergedImages);
            $product->save();

            return $product->images;
        } else {
            return false;
        }
    }
    public function DeleteProductImg(Request $request)
    {
        try {
            $path = $request->input('path');
            $product = Product::find($request->input('id'));
            $images = json_decode($product->images, true);
            $index = array_search($path, $images);
            if (Storage::disk('s3')->has($path)) {
                Storage::disk('s3')->delete($path);
                unset($images[$index]);
                $images = array_values($images);
                $product->thumbnail = isset($images[0]) ? $images[0] : null;
                $product->images = json_encode($images);
                $product->save();
                return $product->images;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
