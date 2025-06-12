<?php session_start();?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotogalerie</title>
    <link rel="icon" type="image/x-icon" href="chlumeclogo.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery-bundle.min.css">

    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/lightgallery.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/thumbnail/lg-thumbnail.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/zoom/lg-zoom.umd.min.js"></script>

    <script src="script.js" defer></script>
</head>
<body>
    <nav>
        <?php include 'header.php'; ?>
    </nav>

    <main>
        <h1>Fotogalerie</h1>

        <div id="albums">
            <div class="album" onclick="openGallery('Zapas1')">Zápas 13. 4. 2025</div>
            <div class="album" onclick="openGallery('Zapas2')">Zápas 20. 4. 2025</div>
        </div>

        <div id="lightgallery" class="gallery-container"></div>
    </main>

    <?php include 'footer.php'; ?>

    <style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        font-family: Arial, sans-serif;
    }
    main {
        flex: 1;
    }

    main h1 {
        text-align: center; 
        margin-top: 40px;   
        margin-bottom: 20px;
    }

    #albums {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 20px;
    }

    .album {
        background: #eee;
        padding: 20px;
        border-radius: 10px;
        cursor: pointer;
        box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
        transition: background 0.3s;
    }

    .album:hover {
        background: #ddd;
    }

    .gallery-container {
        margin: 30px auto;
        max-width: 1000px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    .gallery-container a {
        display: inline-block;
    }

    .gallery-container img {
        width: 150px;
        margin: 5px;
    }
    </style>

<script>
function openGallery(albumName) {
    const gallery = document.getElementById('lightgallery');
    gallery.innerHTML = '';

    const lgInstance = lightGallery(gallery, {destroy: true});
    if (lgInstance) {
        lgInstance.destroy();
    }

    
    const imageCount = 20; 

    for (let i = 1; i <= imageCount; i++) {
        const imagePath = `galerie/${albumName}/${i}.png`;

        const a = document.createElement('a');
        a.href = imagePath;
        a.dataset.src = imagePath;
        a.dataset.subHtml = `<p>Obrázek ${i} z alba ${albumName}</p>`;

        const img = document.createElement('img');
        img.src = imagePath;
        img.alt = `Fotka ${i}`;

        img.onerror = function() { this.style.display='none'; };


        a.appendChild(img);
        gallery.appendChild(a);
    }

    lightGallery(gallery, {
        plugins: [lgZoom, lgThumbnail],
        speed: 500,
        download: false,
        thumbnail: true
    });
}
</script>
</body>
</html>