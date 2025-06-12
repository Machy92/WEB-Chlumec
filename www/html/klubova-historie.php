<?php 
session_start();
include 'header.php'; ?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Warriors Chlumec – Historie klubu</title>
  <link rel="icon" type="image/x-icon" href="chlumeclogo.png" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js"></script>

  <style>
    .hero {
      position: relative;
      background-image: url('images/team_photo.jpg');
      background-size: cover;
      background-position: center;
      height: 400px;
      color: white;
    }
    .hero::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
    }
    .hero-content {
      position: relative;
      z-index: 1;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }
    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
    }
    .timeline {
      border-left: 3px solid #dc3545;
      padding-left: 20px;
      margin-top: 20px;
    }
    .timeline-entry {
      margin-bottom: 20px;
    }
    .social-icons a {
      color: #333;
      text-decoration: none;
      margin-right: 15px;
    }
    .social-icons a:hover {
      color: #dc3545;
    }
  </style>
</head>
<body>

<div class="hero">
  <div class="hero-content">
    <h1>HSÚ SHC Warriors Chlumec</h1>
  </div>
</div>

<section class="container my-5">
  <div class="row">
    <div class="col-lg-6">
      <h2 class="mb-4">Historie klubu</h2>
      <p><strong>HSÚ SHC Warriors Chlumec</strong> je hokejbalový klub založený v roce <strong>2006</strong>. Od té doby si vybudoval silnou pozici v regionální soutěži. Klub je známý svou bojovností, týmovým duchem a věrnými fanoušky.</p>

      <button id="toggleInfoBtn" class="btn btn-danger mt-3">Zobrazit více</button>
      <div id="extraInfo" class="mt-3" style="display: none;">
        <ul>
          <li><strong>Největší úspěch:</strong> Účast ve finále krajské ligy v roce 2019.</li>
          <li><strong>Kapitán týmu:</strong> Patrik Barčák</li>
          <li><strong>Vedoucí klubu:</strong> Vladislav Trnka</li>
          <li><strong>Trenér klubu:</strong> Jan Celner</li>
          <li><strong>Filozofie:</strong> "Srdcem na hřišti, rozumem v týmu."</li>
        </ul>
      </div>

      <p class="mt-4">Sledujte nás na sociálních sítích:</p>
      <div class="social-icons">
        <a href="https://www.facebook.com/..." target="_blank"><i class="fab fa-facebook fa-lg"></i> Facebook</a>
        <a href="https://www.instagram.com/..." target="_blank"><i class="fab fa-instagram fa-lg"></i> Instagram</a>
      </div>
    </div>

    <div class="col-lg-6">
      <h3 class="mb-4">Časová osa</h3>
      <div class="timeline">
        <div class="timeline-entry"><h6>2006</h6><p>Založení klubu a první účast v soutěži.</p></div>
        <div class="timeline-entry"><h6>2010</h6><p>Výhra v okresním přeboru a postup do vyšší ligy.</p></div>
        <div class="timeline-entry"><h6>2015</h6><p>Rekonstrukce hřiště a nový klubový web.</p></div>
        <div class="timeline-entry"><h6>2019</h6><p>Účast ve finále krajské ligy.</p></div>
        <div class="timeline-entry"><h6>2024</h6><p>Spuštění nové moderní webové prezentace klubu.</p></div>
      </div>
    </div>
  </div>
</section>

<section class="container my-5">
  <h2 class="mb-4">Kontaktujte nás</h2>
  <form id="contactForm">
    <div class="mb-3">
      <label for="name" class="form-label">Jméno</label>
      <input type="text" class="form-control" id="name" required />
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">E-mail</label>
      <input type="email" class="form-control" id="email" required />
    </div>
    <div class="mb-3">
      <label for="message" class="form-label">Zpráva</label>
      <textarea class="form-control" id="message" rows="4" required></textarea>
    </div>
    <button type="submit" class="btn btn-danger">Odeslat</button>
    <div id="formStatus" class="mt-3"></div>
  </form>
</section>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("toggleInfoBtn");
    const extraInfo = document.getElementById("extraInfo");

    toggleBtn.addEventListener("click", function () {
      const isVisible = extraInfo.style.display === "block";
      extraInfo.style.display = isVisible ? "none" : "block";
      toggleBtn.textContent = isVisible ? "Zobrazit více" : "Skrýt informace";
    });


    const SUPABASE_URL = "https://opytqyxheeezvwncboly.supabase.co";
    const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9weXRxeXhoZWVlenZ3bmNib2x5Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDc2NDAyMTMsImV4cCI6MjA2MzIxNjIxM30.h_DdvClVy4-xbEkQ3AWQose3dqPaxPQ1gl-LaLhwtCE";

    const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

    const form = document.getElementById("contactForm");
    const statusDiv = document.getElementById("formStatus");

    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      const name = document.getElementById("name").value.trim();
      const email = document.getElementById("email").value.trim();
      const message = document.getElementById("message").value.trim();

      const { error } = await supabase.from("messages").insert([{ name, email, message }]);

      if (error) {
        statusDiv.textContent = "Chyba při odesílání zprávy. Zkuste to prosím znovu.";
        statusDiv.className = "text-danger";
      } else {
        statusDiv.textContent = "Zpráva byla úspěšně odeslána!";
        statusDiv.className = "text-success";
        form.reset();
      }
    });
  });
</script>

</body>
</html>
