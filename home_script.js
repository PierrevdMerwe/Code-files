let quotes = [
  '"Get over here!" ~ Scorpion, Mortal Kombat',
  '"What is better? To be born good or to overcome your evil nature through great effort?" ~ Paarthurnax, The Elder Scrolls 5: Skyrim',
  '"GO DIRECTLY TO JAIL!" ~ Police-man, Monopoly',
  '"War... War never changes." ~ Bethesda Game Studios, Fallout',
  '"A hero need not speak, for when he is gone, the world will speak for him." ~ Halo',
  '"It`s dangerous to go alone! Take this." ~ unnamed old man, The Legend of Zelda',
  '"I used to be an adventurer like you until I took an arrow to the knee." ~ Guard, The Elder Scrolls V: Skyrim',
  '"Fun for the whole family!" ~ every game entertainment company ever',
  '"Ahh man, here we go again." ~ Carl Johnson, GTA San Andreas'
];

let usedQuotes = [];

function showNextQuote() {
  const quoteContainer = document.getElementById('quote-container');

  // If all quotes have been used, reset the arrays
  if (quotes.length === 0) {
    quotes = [...usedQuotes];
    usedQuotes = [];
  }

  // Select a random quote
  const quoteIndex = Math.floor(Math.random() * quotes.length);
  const quote = quotes[quoteIndex];

  // Move the quote from the quotes array to the usedQuotes array
  quotes.splice(quoteIndex, 1);
  usedQuotes.push(quote);

  // Fade out the current quote, then fade in the new quote
  quoteContainer.style.opacity = 0;
  setTimeout(() => {
    quoteContainer.textContent = quote;
    quoteContainer.style.opacity = 1;
  }, 1000);
}

let countdown = 15 * 60; // 15 minutes in seconds

function startCountdown() {
  const countdownElement = document.getElementById('countdown');
  const countdownDigits = countdownElement.getElementsByClassName('countdown-digit');

  // Update the countdown every second
  setInterval(function () {
    const minutes = Math.floor(countdown / 60);
    const seconds = countdown % 60;

    countdownDigits[0].textContent = Math.floor(minutes / 10);
    countdownDigits[1].textContent = minutes % 10;
    countdownDigits[3].textContent = Math.floor(seconds / 10);
    countdownDigits[4].textContent = seconds % 10;

    countdown--;

    // Reset the countdown when it reaches 0
    if (countdown < 0) {
      countdown = 15 * 60;
    }
  }, 1000);
}

function attachBuyNowEventListeners() {
  const buyNowButtons = document.querySelectorAll('.buy-now-button');
  buyNowButtons.forEach(button => {
    button.addEventListener('click', function() {
      console.log('Buy Now button clicked');
      const productId = this.dataset.productId;
      fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`,
      })
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          console.error(data.error);
          alert('Failed to add product to cart. Please try again.');
        } else {
          console.log('Product added to cart');
          alert('Product added to cart successfully!');
        }
      })
      .catch(error => {
        console.error(error);
        alert('An error occurred. Please try again.');
      });
    });
  });
}

window.onload = function () {
  fetch('home_connect.php')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error(data.error);
        return;
      }

      const gridContainer = document.querySelector('.grid-container');
      gridContainer.innerHTML = '';

      for (const product of data.products) {
        console.log(product); // Add this line
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

      const genreContainer = document.querySelector('.genre-container');
      genreContainer.innerHTML = '';

      for (const category of data.categories) {
        const genreBox = document.createElement('div');
        genreBox.classList.add('genre-box');
        genreBox.innerHTML = `
              <h3 class="genre-title">${category.category_name}</h3>
              <img class="genre-image" src="assets/${category.image}" alt="${category.category_name}">
              <p class="genre-description">Discover a wide range of games in this genre.</p>
              <button class="genre-button">Explore ${category.category_name}</button>
          `;
          genreBox.addEventListener('click', function() {
            // Redirect to the categories_catalogue.html page with the category ID as a query parameter
            window.location.href = 'categories_catalogue.html';
        });
        genreContainer.appendChild(genreBox);
      }

      // Attach event listeners after dynamic content is loaded
      attachBuyNowEventListeners();
    })
    .catch(error => {
      console.error(error);
    });

  showNextQuote();
  setInterval(showNextQuote, 10000);
  startCountdown();
};
