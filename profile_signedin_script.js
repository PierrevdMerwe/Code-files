$(document).ready(function () {
    // Fetch the user's details
    $.ajax({
        type: 'GET',
        url: 'profile_signedin_connect.php',
        success: function (response) {
            console.log('Response from server:', response);  // Log the response from the server
            var data = JSON.parse(response);
            if (data.error) {
                console.log('Error:', data.error);  // Log the error
            } else {
                // Display the user's details
                var welcomeMessage = document.getElementById('welcome-message');
                welcomeMessage.innerHTML = `Welcome to your profile <span style="color: #ff8c00;">${data.first_name} ${data.last_name}</span>!`;


                // Display the user's order history
                var orderHistory = document.getElementById('order-history');
                var tableHTML = '<table><tr><th>Order ID</th><th>Date Ordered</th><th>Status</th><th>Product Name</th><th>Quantity</th></tr>';
                for (var i = 0; i < data.orders.length; i++) {
                    var order = data.orders[i];
                    var productName = data.products[order.product_id.toString()];  // Get the product name from the products object
                    tableHTML += `
                        <tr>
                            <td>${order.order_id}</td>
                            <td>${order.date_ordered}</td>
                            <td>${order.status}</td>
                            <td>${productName}</td>
                            <td>${order.quantity}</td>
                        </tr>
                    `;
                }
                tableHTML += '</table>';
                orderHistory.innerHTML = tableHTML;
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('AJAX error:', textStatus, errorThrown);  // Log any AJAX errors
        }
    });

    // Handle the sign-out button click event
    var signoutButton = document.getElementsByClassName('signout-button')[0];
    signoutButton.addEventListener('click', function () {
        $.ajax({
            type: 'POST',
            url: 'signout.php',
            success: function (response) {
                var data = JSON.parse(response);
                if (data.signedOut) {
                    window.location.href = "profile.html";  // Redirect to login page after signing out
                }
            }
        });
    });
});
