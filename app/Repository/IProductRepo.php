<?php

namespace App\Repository;

use Illuminate\Http\Request;

interface IProductRepo
{
    public function FetchProducts($id);
    public function GetAvgRating($id);
    public function GetProduct($id);
    public function GetFeaturedProducts($id);
    public function FetchRelatedProducts($id);
    public function AddProduct($validatedData);
    public function DeleteProduct($id);
    public function UpdateProduct(Request $request);
    public function AddCategory($validatedData);
    public function AddSubCategory($validatedData);
    public function FetchCategories($id);
    public function FetchSearchResults(Request $request);
    public function StockUpdate($id, $stock);
    public function AddProductImgs(Request $request);
    public function UpdateProductImgs(Request $request);
    public function DeleteProductImg(Request $request);
}
