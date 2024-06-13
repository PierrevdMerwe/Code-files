$(document).ready(function () {
    // Fetch the user's cart items
    $.ajax({
        type: 'GET',
        url: 'cart_connect.php',
        success: function (response) {
            console.log('Response from server:', response);  // Log the response from the server
            var data = JSON.parse(response);
            if (data.error) {
                console.log('Error:', data.error);  // Log the error
            } else {
                // Display the user's cart items
                var cartItems = document.getElementById('cart-items');
                var orderItems = document.getElementById('order-items');
                var tableHTML = '';
                var orderHTML = '';
                var total = 0;

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    var itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    
                    tableHTML += `
                        <div class="cart-item">
                            <img src="assets/${item.image}" alt="${item.product_name}">
                            <div class="product-details">
                                <h4>${item.product_name}</h4>
                                <p>${item.description}</p>
                                <p>R${item.price}</p>
                            </div>
                            <div class="item-actions">
                                <button class="remove-item" data-product-id="${item.product_id}">Remove</button>
                                <div class="quantity">
                                    <label for="quantity-${item.product_id}">Qty: </label>
                                    <input type="number" id="quantity-${item.product_id}" name="quantity-${item.product_id}" value="${item.quantity}" min="1">
                                </div>
                            </div>
                        </div>
                    `;

                    orderHTML += `
                        <p>${item.quantity} x ${item.product_name} <span style="float: right;">R${itemTotal}</span></p>
                    `;
                }
                
                cartItems.innerHTML = tableHTML;
                orderItems.innerHTML = orderHTML;
                document.getElementById('total-price').textContent = 'R' + total;

                // Event listener for remove buttons
                $('.remove-item').click(function () {
                    var productId = $(this).data('product-id');
                    $.ajax({
                        type: 'POST',
                        url: 'cart_connect.php',
                        data: { product_id: productId, remove: true },
                        success: function (response) {
                            console.log('Remove response:', response);
                            location.reload();  // Refresh the page
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log('AJAX error:', textStatus, errorThrown);
                        }
                    });
                });

                // Event listener for quantity input change
                $('input[type="number"]').change(function () {
                    var newQuantity = $(this).val();
                    var productId = $(this).attr('id').split('-')[1];
                    $.ajax({
                        type: 'POST',
                        url: 'cart_connect.php',
                        data: { product_id: productId, quantity: newQuantity },
                        success: function (response) {
                            console.log('Update response:', response);
                            location.reload();
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log('AJAX error:', textStatus, errorThrown);
                        }
                    });
                });

                // Event listener for checkout button
                $('#proceed-checkout').click(function () {
                    $.ajax({
                        type: 'POST',
                        url: 'checkout.php',
                        success: function (response) {
                            console.log('Checkout response:', response);
                            var data = JSON.parse(response);
                            if (data.success) {
                                alert('Order placed successfully!');
                                location.reload();  // Refresh the page
                            } else {
                                alert('Error placing order: ' + data.error);
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log('AJAX error:', textStatus, errorThrown);
                            alert('Error placing order. Please try again.');
                        }
                    });
                });
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('AJAX error:', textStatus, errorThrown);  // Log any AJAX errors
        }
    });
});
