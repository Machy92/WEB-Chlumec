<header class="header <?php 
    if (isset($_SESSION["user_id"])) { echo 'logged-in'; }
    if (isset($_SESSION["pozice"]) && $_SESSION["pozice"] === "admin") { echo ' admin-view'; }
?>">  
  <div class="triangle-bg"></div>
  <div class="header-content">
    <div class="logo">
      <a href="index.php">
        <img src="chlumeclogo.png" alt="Warriors Logo">
      </a>
    </div>
    <nav class="menu-container">
      <ul class="menu">
        <li><a href="soupisky.php">Soupiska</a></li>
        <li><a href="zapasy.php">Zápasy</a></li>
        <li><a href="aktuality.php">Aktuality</a></li>
        <li><a href="multimedia.php">Fotogalerie</a></li>
        <li><a href="klubova-historie.php">Klub</a></li>
        <?php if (isset($_SESSION["user_id"])): ?>
          <?php if (isset($_SESSION["pozice"]) && $_SESSION["pozice"] === "admin"): ?>
            <li><a href="sprava_uzivatelu.php">Správa</a></li>
          <?php endif; ?>
          <li><a href="profil.php">Profil</a></li> 
          <li><a href="logout.php">Odhlásit</a></li>
        <?php else: ?>
          <li><a href="login.php">Přihlášení</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="menu-toggle">&#9776;</div>
  </div>
</header>

<style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; /* Přidáno pro jistotu hezkého systémového fontu */
  }
  main {
    flex: 1;
  }


  .header {
    position: relative;
    height: 100px;
    z-index: 1000;
    color: white;
    width: 100%;
    transition: all 0.3s ease;
  }

  .triangle-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100px;
    background: linear-gradient(115deg, #d32f2f 210px, #000 210px);
    clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
    z-index: 0;
    animation: fadeInDown 1s ease-out;
  }

  .header-content {
    max-width: 1200px;
    margin: 0 auto;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
  }

  .logo img {
    max-width: 120px;
    height: auto;
    animation: fadeInLeft 1s ease-out;
  }

  .menu-container {
    display: flex;
    z-index: 999;
  }

  .menu {
    display: flex;
    gap: 20px; 
    list-style: none;
    margin: 0;
    padding: 0;
    transition: gap 0.3s ease;
  }

  .menu li a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 14px;
    border-radius: 5px;
    transition: all 0.3s ease;
    position: relative;
    white-space: nowrap; 
  }

  .menu li a:hover {
    background-color: rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
  }

  .menu-toggle {
    display: none;
    font-size: 28px;
    cursor: pointer;
    color: white;
    z-index: 1001;
  }

  @media (min-width: 1400px) {
    .header-content {
      max-width: 1600px;
      padding: 0 60px;
    }
    .menu li a {
        font-size: 1.1rem;
        padding: 10px 20px;
    }

    .admin-view .menu {
        gap: 15px;
    }
    .admin-view .menu li a {
        padding: 10px 15px;
        font-size: 1rem;
    }
  }

  @media (min-width: 2000px) {
    .menu {
        gap: 50px;
    }
    .admin-view .menu {
        gap: 25px;
    }
  }


  @media (max-width: 1035px) {
    .menu-toggle {
      display: block;
    }

    .menu-container {
      display: none;
      flex-direction: column;
      position: absolute;
      top: 100px;
      left: 0;
      width: 100%;
      background-color: #000;
      padding: 15px 0;
      z-index: 1000;
    }

    .menu-container.active {
      display: flex;
    }

    .menu {
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }
  }

  @media (max-width: 768px) {
    .triangle-bg {
      background: linear-gradient(115deg, #d32f2f 80px, #000 80px);
    }
  }

  @keyframes fadeInDown {
    from { transform: translateY(-100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }

  @keyframes fadeInLeft {
    from { transform: translateX(-50px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector(".menu-toggle");
    const menu = document.querySelector(".menu-container");

    toggle.addEventListener("click", () => {
      menu.classList.toggle("active");
    });

    document.querySelectorAll(".menu a").forEach(link => {
      link.addEventListener("click", (e) => {
        if (link.getAttribute('href') === '#' && window.innerWidth <= 1035) {
            e.preventDefault();
        }
        if (window.innerWidth <= 1035) {
          menu.classList.remove("active");
        }
      });
    });
  });
</script>