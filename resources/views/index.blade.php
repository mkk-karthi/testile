@extends('layout')

@section('content')
    <div class="container-fluid my-3">
        <div class="card">
            <div class="card-title m-2 d-flex justify-content-between border-bottom">
                <h5 class="m-2">Products</h5>
                <a href="{{ route('product.create') }}" class="btn btn-success m-2"><i class="bi bi-plus-lg"></i></a>
            </div>
            <div id="messages"> </div>

            <div class="card-body p-2">

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Image</th>
                                <th scope="col">Country</th>
                                <th scope="col">State</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $key => $product)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td><img src="/{{ $product->product_image }}" alt="{{ $product->product_name }}"
                                            width="60" /></td>
                                    <td>{{ $product->state->country->country_name }}</td>
                                    <td>{{ $product->state->state_name }}</td>
                                    <td>
                                        <a href="/edit/{{ $product->id }}" class="btn btn-success my-1">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger my-1 delete-product"
                                            data-id="{{ $product->id }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $products->OnEachSide(1)->links() }}
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteModalLabel">Product Delete</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure to delete this product?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" id="delete-conform">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(() => {

            let productId = 0;
            $(".delete-product").each(function() {
                $(this).click(function() {
                    productId = $(this).attr("data-id");
                    $("#deleteModal").modal("show")
                })
            })

            $("#delete-conform").click(() => {
                if (productId > 0) {
                    $.ajax({
                        url: `${location.origin}/delete/${productId}`,
                        type: 'post',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')
                        },
                        success: (res) => {
                            productId = 0;
                            $("#deleteModal").modal("hide")
                            if (res.code == 0) {
                                const msgContent =
                                    `<div class="alert alert-success" role="alert">${res.message}</div>`;
                                $("#messages").append(msgContent)

                                setTimeout(() => {
                                    location.reload();
                                }, 3000);

                            } else {
                                const msgContent =
                                    `<div class="alert alert-danger" role="alert">${res.message}</div>`;
                                $("#messages").append(msgContent)

                                setTimeout(() => {
                                    $("#messages").html($("#messages").html()
                                        .replace(msgContent, ""))
                                }, 5000);

                            }
                        },
                        error: (err) => {
                            $("#product-form-submit").prop("disabled", false)
                            console.log(err)
                        }
                    })
                }

            })
        })
    </script>
@endsection
