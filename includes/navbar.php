<nav class="navbar"><a class="navbar-brand" href="/larong_pinoy/index.php"><div class="navbar-logo"><img src="/larong_pinoy/assets/images/philippines-logo.png" alt="Philippines logo"></div><div><div class="navbar-title">Larong Pinoy</div><div class="navbar-subtitle">Traditional Filipino Games</div></div></a>
<div class="navbar-links" id="navLinks">
<a href="/larong_pinoy/games/list.php">Games</a>
<?php if (isLoggedIn()): ?>
<a href="/larong_pinoy/user/favorites.php">Favorites</a>
<a href="/larong_pinoy/user/profile.php">Profile</a>
<?php if (isAdmin()): ?><a href="/larong_pinoy/admin/dashboard.php">Admin</a><?php endif; ?>
<a class="nav-btn" href="/larong_pinoy/logout.php">Logout</a>
<?php else: ?>
<a href="/larong_pinoy/login.php">Login</a><a class="nav-btn" href="/larong_pinoy/register.php">Register</a>
<?php endif; ?>
<button type="button" id="themeToggle" class="btn btn-outline theme-toggle">Dark Mode</button>
</div></nav>
