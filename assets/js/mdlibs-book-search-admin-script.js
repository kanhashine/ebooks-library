var slider = document.getElementById("libs-bookprice");
var output = document.getElementById("book_price_val");
output.innerHTML = slider.value;

slider.oninput = function() {
  output.innerHTML = this.value;
}