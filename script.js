let cart = JSON.parse(localStorage.getItem("cart")) || [];

// ✅ إضافة عنصر إلى السلة
function addToCart(name, price) {
  const existing = cart.find(item => item.name === name);
  if (existing) {
    existing.quantity += 1;
  } else {
    cart.push({ name, price, quantity: 1 });
  }
  localStorage.setItem("cart", JSON.stringify(cart));
  alert(name + " تم إضافته إلى الطلبات ✅");
}

// ✅ عرض العناصر في صفحة cart.html
function renderCart() {
  const container = document.querySelector(".cart-items");
  if (!container) return;

  container.innerHTML = "";
  if (cart.length === 0) {
    container.innerHTML = "<p>لا توجد طلبات بعد 🥲</p>";
    return;
  }

  let total = 0;

  cart.forEach((item, index) => {
    const div = document.createElement("div");
    div.className = "cart-item";
    div.innerHTML = `
      <h3>${item.name}</h3>
      <p>السعر: ${item.price} دج</p>
      <p>الكمية:
        <button onclick="updateQuantity(${index}, -1)">➖</button>
        ${item.quantity}
        <button onclick="updateQuantity(${index}, 1)">➕</button>
      </p>
      <hr>
    `;
    total += item.price * item.quantity;
    container.appendChild(div);
  });

  document.getElementById("total").textContent = total + " دج";
}

// ✅ تحديث الكمية
function updateQuantity(index, change) {
  cart[index].quantity += parseInt(change);
  if (cart[index].quantity <= 0) {
    cart.splice(index, 1);
  }
  localStorage.setItem("cart", JSON.stringify(cart));
  renderCart();
}

// ✅ عند إتمام الطلب - حفظ الطلبات
function checkout() {
  localStorage.setItem("lastOrder", JSON.stringify(cart));
  cart = [];
  localStorage.removeItem("cart");
  window.location.href = "checkout.html";
}

// ✅ في صفحة الطلبات - عرض الطلب الأخير
function renderLastOrder() {
  const container = document.querySelector(".last-orders");
  if (!container) return;

  const last = JSON.parse(localStorage.getItem("lastOrder")) || [];

  if (last.length === 0) {
    container.innerHTML = "<p>لم تقم بأي طلب بعد.</p>";
    return;
  }

  last.forEach(item => {
    const div = document.createElement("div");
    div.innerHTML = `
      <h4>${item.name}</h4>
      <p>الكمية: ${item.quantity}</p>
      <p>المجموع: ${item.price * item.quantity} دج</p>
      <hr>
    `;
    container.appendChild(div);
  });
}

// ✅ إرسال معلومات الطلب إلى السيرفر
function submitForm(event) {
  event.preventDefault();

  const form = document.querySelector(".checkout-form");
  const data = {
    full_name: form.querySelector("input[type='text']").value,
    address: form.querySelectorAll("input")[1].value,
    phone: form.querySelector("input[type='tel']").value,
    payment_method: form.querySelector("select").value,
    cart: cart
  };

  fetch("submit_order.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(data)
  })
  .then(res => res.json())
  .then(res => {
    alert(res.message);
    localStorage.removeItem("cart");
    window.location.href = "index.html";
  })
  .catch(err => {
    alert("حدث خطأ أثناء إرسال الطلب");
    console.error(err);
  });
}

