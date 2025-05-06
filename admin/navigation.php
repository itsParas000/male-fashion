<ul>
<li>
    <a href="#">
        <span class="icon">
            <ion-icon name="person-circle"></ion-icon>
        </span>
        <span class="title">Admin Panel</span>
        </a>
    </li>
  <li>
    <a href="dashboard.php">
      <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
      <span class="title">Dashboard</span>
    </a>
  </li>
  <li>
    <a href="users.php">
      <span class="icon"><ion-icon name="people-outline"></ion-icon></span>
      <span class="title">Users</span>
    </a>
  </li>
  <li>
    <a href="products.php">
      <span class="icon"><ion-icon name="cart-outline"></ion-icon></span>
      <span class="title">Products</span>
    </a>
  </li>
  <li>
    <a href="orders.php">
      <span class="icon"><ion-icon name="bag-outline"></ion-icon></span>
      <span class="title">Orders</span>
    </a>
  </li>
  <li>
    <a href="coupons.php">
      <span class="icon"><ion-icon name="pricetags-outline"></ion-icon></span>
      <span class="title">Coupons</span>
    </a>
  </li>
  <li>
    <a href="contact_messages.php">
      <span class="icon"><ion-icon name="chatbubble-outline"></ion-icon></span>
      <span class="title">Messages</span>
    </a>
  </li>
  <li>
    <a href="../php/logout.php?role=admin" onclick="return confirmLogout()">
      <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
      <span class="title">Sign Out</span>
    </a>
  </li>
</ul>
<script>
    function confirmLogout() {
            return confirm("Are you sure you want to sign out?");
        }
</script>
