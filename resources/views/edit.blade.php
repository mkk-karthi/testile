@extends('layout')

@section('content')
    <div class="container-fluid my-3">
        <div class="card">
            <div class="card-title m-2 d-flex justify-content-between border-bottom">
                <h5 class="m-2">Product Create</h5>
            </div>

            <div class="card-body p-2">
                <form id="product-form">
                    @csrf
                    <input type="text" name="product_id" value="{{ $product['product_id'] }}" hidden id="product_id">

                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Product name</label>
                                <input type="text" name="name" value="{{ $product['product_name'] }}"
                                    class="form-control" autocomplete="off" id="name">
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Product Country</label>
                                <select class="form-select" name="country" id="country" autocomplete="off">
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                            @if ($product['country_id'] == $country->id) 'selected' @endif>
                                            {{ $country->country_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="invalid-feedback" id="country-error"></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="mb-3">
                                <input type="text" name="state_id" value="{{ $product['state_id'] }}" hidden
                                    id="state_id">
                                <label class="form-label">Product State</label>
                                <select class="form-select" name="state" id="state" autocomplete="off">
                                </select>

                                <div class="invalid-feedback" id="state-error"></div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Product image</label>
                                <input type="file" name="image" class="form-control" autocomplete="off"
                                    accept="image/*" id="image">

                                <div class="invalid-feedback" id="image-error"></div>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-between">
                            <p class="fs-6 fw-bold">Inventory Details</p>
                            <button type="button" class="btn btn-success m-2" id="add-inventory">Add new</button>
                        </div>
                        <div class="col-12 mb-3" id="inventory-details">
                        </div>
                        <div class="col-12" id="messages"> </div>


                        <div class="col-12 text-end">
                            <a href="/" class="btn btn-outline-secondary m-2" id="product-form-submit">Close</a>
                            <button type="button" class="btn btn-primary m-2" id="product-form-submit">Submit</button>
                        </div>

                    </div>
                </form>

            </div>

        </div>
    </div>

    <div class="modal fade hide" id="errorModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Error!</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>


    <script>
        $(() => {
            const StateId = $("#state_id").val();
            const ProductId = $("#product_id").val();

            const getStateOptions = (id, emptyValue = true) => {

                $.get(`${location.origin}/state_options/${id}`, (res) => {
                    var content = "";

                    if (res.data) {
                        const data = res.data;
                        for (index in res.data) {
                            var selected = StateId == index ? 'selected' : '';
                            content +=
                                `<option value="${index}" ${selected}>${res.data[index]}</option>`;
                        }
                    }

                    $("#state").html(content);

                    if (emptyValue)
                        $("#state").val("");
                })
            }
            $("#country").change((ele) => {
                const id = ele.target.value;
                $("#state").val("");
                getStateOptions(id);
            })

            const country_id = $("#country").val();
            if (country_id && country_id != "Select Country") {
                getStateOptions(country_id, false)
            }

            $("#product-form-submit").click(() => {
                $("#product-form-submit").prop("disabled", true)

                // get values
                const name = $("#name").val();
                const country = $("#country").val();
                const state = $("#state").val();
                const image = $("#image").val();
                let file = document.getElementById('image');
                if (file) {
                    file = file.files[0]
                }

                var inputData = new FormData();

                // validate inputs
                let errors = {
                    name: "",
                    country: "",
                    state: "",
                    image: ""
                };

                if (!name) errors.name = "Name is required";
                else if (name.length > 120) errors.name = "Name may not be greater than 120 characters";

                if (!country) errors.country = "Country is required";
                if (!state) errors.state = "State is required";
                if (image && file.size > 500 * 1024) errors.image = "Image may not be greater than 500KB.";
                else if (image && !["image/jpg", "image/jpeg", "image/png"].includes(file.type))
                    errors.image = "Image must be a file of type: jpg, jpeg, png.";

                // inventory validation
                $(`#inventory-details`).children().each(function() {
                    const inventoryId = $(this).data("id")

                    // get values
                    const size = $(`#inventory-${inventoryId}-size`).val();
                    const lengths = $(`#inventory-${inventoryId}-lengths`).val();
                    const quantity = $(`#inventory-${inventoryId}-quantity`).val();

                    errors[`inventory-${inventoryId}-size`] = "";
                    errors[`inventory-${inventoryId}-lengths`] = "";
                    errors[`inventory-${inventoryId}-quantity`] = "";

                    if (!size) errors[`inventory-${inventoryId}-size`] = "Size is required";
                    if (!lengths) errors[`inventory-${inventoryId}-lengths`] = "Length is required";
                    if (!quantity) errors[`inventory-${inventoryId}-quantity`] =
                        "Quantity is required";

                    if (errors[`inventory-${inventoryId}-size`] ||
                        errors[`inventory-${inventoryId}-lengths`] ||
                        errors[`inventory-${inventoryId}-quantity`]) {
                        inventoryError = true;
                    } else {

                        inputData.append(`inventory[${inventoryId}][size]`, size)
                        inputData.append(`inventory[${inventoryId}][length]`, lengths)
                        inputData.append(`inventory[${inventoryId}][quantity]`, quantity)
                    }
                })

                // print error
                let ErrorFound = false;
                for (const [key, error] of Object.entries(errors)) {
                    if (errors[key]) {
                        ErrorFound = true;
                        $(`#${key}`).addClass("is-invalid");
                    } else {
                        $(`#${key}`).removeClass("is-invalid");
                    }
                    $(`#${key}-error`).text(errors[key]);
                }

                if (!ErrorFound) {
                    inputData.append("id", ProductId)
                    inputData.append("name", name)
                    inputData.append("country", country)
                    inputData.append("state", state)
                    if (file)
                        inputData.append("image", file)

                    $.ajax({
                        url: `${location.origin}/update`,
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')
                        },
                        data: inputData,
                        processData: false,
                        contentType: false,
                        success: (res) => {
                            if (res.code == 1) {
                                $("#product-form-submit").prop("disabled", false)

                                for (let [key, value] of Object.entries(res.errors)) {
                                    key = key.replaceAll("length", "lengths").replaceAll(".",
                                        "-")

                                    $(`#${key}`).addClass("is-invalid");
                                    $(`#${key}-error`).text(value.join(" "));
                                }
                            } else if (res.code == 2) {
                                $("#product-form-submit").prop("disabled", false)

                                // show error message
                                const msgContent =
                                    `<div class="alert alert-danger" role="alert">${res.message}</div>`;
                                $("#messages").append(msgContent)

                                setTimeout(() => {
                                    $("#messages").html($("#messages").html()
                                        .replace(msgContent, ""))
                                }, 5000);
                            } else {
                                // show success message
                                const msgContent =
                                    `<div class="alert alert-success" role="alert">${res.message}</div>`;
                                $("#messages").html(msgContent)

                                setTimeout(() => {
                                    location.href = location.origin;
                                }, 3000);
                            }
                        },
                        error: (err) => {
                            $("#product-form-submit").prop("disabled", false)
                            console.log(err)
                        }
                    })
                } else {
                    $("#product-form-submit").prop("disabled", false)
                }

            })

            var inventoryKey = 0;
            const addInventory = () => {
                if ($(`#inventory-details`).children().length < 3) {
                    $("#inventory-details").append(`<div class="card my-1" id="inventory-${inventoryKey}" data-id="${inventoryKey}">
                                <div class="card-body p-2">
                                    <div class="row">
                                        <div class="col-12 col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Size</label>
                                                <input type="text" name="inventory.${inventoryKey}.size" value=""
                                                    class="form-control" autocomplete="off" id="inventory-${inventoryKey}-size">
                                                <div class="invalid-feedback" id="inventory-${inventoryKey}-size-error"></div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Length</label>
                                                <input type="number" name="inventory.${inventoryKey}.lengths" value=""
                                                    class="form-control" autocomplete="off" id="inventory-${inventoryKey}-lengths">
                                                <div class="invalid-feedback" id="inventory-${inventoryKey}-lengths-error"></div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="inventory.${inventoryKey}.quantity" value=""
                                                    class="form-control" autocomplete="off" id="inventory-${inventoryKey}-quantity">
                                                <div class="invalid-feedback" id="inventory-${inventoryKey}-quantity-error"></div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 text-center">
                                            <button type="button" class="btn btn-danger mx-2 my-4"  id="delete-${inventoryKey}-inventory" data-id="${inventoryKey}"><i
                                                    class="bi bi-trash3"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>`)


                    $(`#delete-${inventoryKey}-inventory`).click(function() {

                        // check at least 1 inventory is required
                        if ($(`#inventory-details`).children().length > 1) {

                            let id = $(this).attr("data-id");
                            $(`#inventory-${id}`).remove();
                        } else {

                            $("#errorModal").modal("show")
                            $(".modal-body").html(
                                "<p class='text-danger'>At least 1 inventory is required</p>")
                        }
                    })

                    inventoryKey++;
                } else {
                    $("#errorModal").modal("show")
                    $(".modal-body").html(
                        "<p class='text-danger fs-5'>Max 3 inventories only</p>")
                }
            }

            $("#add-inventory").click(() => addInventory())

            @foreach ($product['inventory'] as $row)
                addInventory();

                $(`#inventory-${inventoryKey-1}-size`).val("{{ $row->size }}");
                $(`#inventory-${inventoryKey-1}-lengths`).val("{{ $row->length }}");
                $(`#inventory-${inventoryKey-1}-quantity`).val("{{ $row->quantity }}");
            @endforeach


        })
    </script>
@endsection
