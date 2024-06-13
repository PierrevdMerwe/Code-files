const cardsContainer = document.querySelector(".container");

cardsContainer.addEventListener("click", (e) => {
    const target = e.target.closest(".card");

    if (!target) return;

    cardsContainer.querySelectorAll(".card").forEach((card) => {
        card.classList.remove("active");
    });

    target.classList.add("active");
});

let gridItems; // We will assign this later after fetching the data

// Function to show a specific number of items
function showItems(count) {
    for (let i = 0; i < count; i++) {
        if (gridItems[i]) {
            gridItems[i].style.display = "block";
        }
    }
}

window.onload = function () {
    fetch('categories_catalogue_connect.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            let products = data.products;
            let categories = data.categories;

            const headingContainer = document.querySelector('.heading-container');
            headingContainer.innerHTML = '';

            for (const category of categories) {
                const genreBox = document.createElement('div');
                genreBox.classList.add('genre-box');
                genreBox.textContent = category.category_name;
                genreBox.addEventListener('click', function() {
                    const categoryId = category.category_id === 1 ? 'all' : category.category_id;
                    fetch('categories_catalogue_connect.php?category_id=' + categoryId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                console.error(data.error);
                                return;
                            }

                            displayProducts(data.products);
                        })
                        .catch(error => console.error('Error:', error));

                    headingContainer.querySelectorAll('.genre-box').forEach(box => box.classList.remove('selected'));
                    genreBox.classList.add('selected');
                });
                headingContainer.appendChild(genreBox);
            }

            function displayProducts(productsToDisplay) {
                console.log(productsToDisplay);
                const gridContainer = document.querySelector('.grid-container');
                gridContainer.innerHTML = '';
                for (const product of productsToDisplay) {
                    const gridItem = document.createElement('div');
                    gridItem.classList.add('grid-item');
                    gridItem.innerHTML = `
                    <div class="post-image">
                        <img src="assets/${product.image}" alt="${product.product_name}">
                    </div>
                    <h3 class="post-title">${product.product_name}</h3>
                    <p class="post-body">${product.description}</p>
                    <div class="post-price-button">
                        <p class="post-price">Price: R${product.price}</p>
                        <button class="buy-now-button" data-product-id="${product.product_id}">Buy Now</button>
                    </div>
                `;
                    gridContainer.appendChild(gridItem);
                }

                // Add event listeners to the "Buy Now" buttons
                const buyNowButtons = document.querySelectorAll('.buy-now-button');
                buyNowButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const productId = this.getAttribute('data-product-id');
                        addToCart(productId);
                    });
                });
            }

            // Display all products initially
            displayProducts(products);
            // Make 'All' genre selected initially
            headingContainer.querySelector('.genre-box').classList.add('selected');
        })
        .catch(error => console.error('Error:', error));
};

function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added to cart successfully!');
        } else {
            alert('Failed to add product to cart.');
        }
    })
    .catch(error => console.error('Error:', error));
}
