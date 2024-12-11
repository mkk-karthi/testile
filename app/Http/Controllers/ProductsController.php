<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Inventory;
use App\Models\Products;
use App\Models\State;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get products
        $products = Products::with(["state.country", "inventory"])->paginate(10);

        return view('index', ["products" => $products]);
    }
    public function state_options($id)
    {
        $states = State::where([["country_id", $id]])->get()->pluck("state_name", "id");
        return response()->json(["data" => $states]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::all();
        return view('create', ["countries" => $countries]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // validation
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:120",
            "country" => "required|exists:countries,id",
            "state" => "required|exists:states,id",
            "image" => "required|file|mimes:png,jpeg,jpg|max:512",
            "inventory" => "required|array",
            "inventory.*.size" => "required|string",
            "inventory.*.length" => "required|string",
            "inventory.*.quantity" => "required|numeric",
        ], [
            "inventory.*.size.required" => "Size is required.",
            "inventory.*.size.string" => "Size must be a string.",
            "inventory.*.length.required" => "Length is required.",
            "inventory.*.length.string" => "Length must be a string.",
            "inventory.*.quantity.required" => "Quantity is required.",
            "inventory.*.quantity.numeric" => "Quantity must be a number.",
        ]);

        // validation errors
        if ($validator->fails()) {
            return response()->json(["code" => 1, "errors" => $validator->errors()->toArray(), "input" => $request->all()]);
        } else {
            $inputs = $request->all();
            $uploaded_path = "";

            try {
                // start tansaction
                DB::beginTransaction();

                // file upload
                $file = $request->file("image");

                $file_name = $file->getClientOriginalName();
                $file_name = time() . "-" . $file_name;
                $uploaded_path = Storage::disk("public_upload")->putFileAs("upload", $file, $file_name);

                // insert products
                $product_input = [
                    "product_name" => $inputs["name"],
                    "product_image" => $uploaded_path,
                    "product_state_id" => $inputs["state"],
                ];

                $product = Products::create($product_input);

                if ($product) {
                    $product_id = $product->id;

                    // insert inventory details
                    foreach ($inputs["inventory"] as $inventory) {

                        $inventory_input = [
                            "size" => $inventory["size"],
                            "length" => $inventory["length"],
                            "quantity" => $inventory["quantity"],
                            "product_id" => $product_id,
                        ];

                        Inventory::create($inventory_input);
                    }
                }

                DB::commit();

                return response()->json(["code" => 0, "message" => "Product succesfully created"]);
            } catch (Exception $ex) {
                DB::rollBack();

                // delete uploaded file
                if ($uploaded_path)
                    Storage::disk("public_upload")->delete($uploaded_path);

                return response()->json(["code" => 2, "message" => $ex->getMessage()]);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(products $products)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // get product
        $product = Products::with(["state.country", "inventory"])->where("id", $id)->first();

        if (is_null($product)) {
            return abort(404);
        } else {

            // get countries
            $countries = Country::all();

            $response_data = [
                "product_id" => $product->id,
                "product_name" => $product->product_name,
                "state_id" => $product->state->id,
                "country_id" => $product->state->country->id,
                "inventory" => $product->inventory,
            ];

            return view('edit', ["countries" => $countries, "product" => $response_data]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "id" => "required|exists:products,id",
            "name" => "required|string|max:120",
            "country" => "required|exists:countries,id",
            "state" => "required|exists:states,id",
            "image" => "nullable|file|mimes:png,jpeg,jpg|max:512",
            "inventory" => "required|array",
            "inventory.*.size" => "required|string",
            "inventory.*.length" => "required|string",
            "inventory.*.quantity" => "required|numeric",
        ], [
            "inventory.*.size.required" => "Size is required.",
            "inventory.*.size.string" => "Size must be a string.",
            "inventory.*.length.required" => "Length is required.",
            "inventory.*.length.string" => "Length must be a string.",
            "inventory.*.quantity.required" => "Quantity is required.",
            "inventory.*.quantity.numeric" => "Quantity must be a number.",
        ]);

        if ($validator->fails()) {
            return response()->json(["code" => 1, "errors" => $validator->errors()->toArray(), "input" => $request->all()]);
        } else {
            $inputs = $request->all();
            $uploaded_path = "";

            try {
                // start tansaction
                DB::beginTransaction();

                $product_id = $inputs['id'];

                // file upload
                if ($file = $request->file("image")) {

                    $file_name = $file->getClientOriginalName();
                    $file_name = time() . "-" . $file_name;
                    $uploaded_path = Storage::disk("public_upload")->putFileAs("upload", $file, $file_name);
                }

                $product = Products::where("id", $product_id)->first();
                $product_image = $product->product_image;

                // insert products
                $product_input = [
                    "product_name" => $inputs["name"],
                    "product_state_id" => $inputs["state"]
                ];

                if ($uploaded_path) {
                    $product_input["product_image"] = $uploaded_path;
                }

                $update_product = Products::where("id", $product_id)->update($product_input);

                if ($update_product) {
                    $product_id = $inputs['id'];

                    $inventory_ids = Inventory::where("product_id", $product_id)->get()->pluck("id");

                    // insert inventory details
                    foreach ($inputs["inventory"] as $key => $inventory) {

                        $inventory_input = [
                            "size" => $inventory["size"],
                            "length" => $inventory["length"],
                            "quantity" => $inventory["quantity"],
                            "product_id" => $product_id
                        ];

                        if (isset($inventory_ids[$key]) && $inventory_ids[$key]) {
                            Inventory::where("id", $inventory_ids[$key])->update($inventory_input);
                        } else {
                            Inventory::create($inventory_input);
                        }
                    }

                    if (count($inputs["inventory"]) < count($inventory_ids)) {
                        $inventory_ids = $inventory_ids->toArray();
                        $inventory_ids = array_slice($inventory_ids, count($inputs["inventory"]));
                        Inventory::whereIn("id", $inventory_ids)->delete();
                    }

                    // delete old uploaded file
                    if ($uploaded_path && $product_image)
                        Storage::disk("public_upload")->delete($product_image);
                }

                DB::commit();

                return response()->json(["code" => 0, "message" => "Product succesfully updated"]);
            } catch (Exception $ex) {
                DB::rollBack();

                // delete uploaded file
                if ($uploaded_path)
                    Storage::disk("public_upload")->delete($uploaded_path);

                return response()->json(["code" => 2, "message" => $ex->getMessage()]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            $product = Products::find($id);

            if (is_null($product)) {
                return response()->json(["code" => 2, "message" => "Product not found"]);
            } else {
                DB::beginTransaction();


                // delete inventory
                Inventory::where("product_id", $id)->delete();

                // delete product
                Products::where("id", $id)->delete();

                // delete uploaded file
                if ($product->product_image)
                    Storage::disk("public_upload")->delete($product->product_image);

                DB::commit();
                return response()->json(["code" => 0, "message" => "Product succesfully deleted"]);
            }
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json(["code" => 2, "message" => $ex->getMessage()]);
        }
    }
}
