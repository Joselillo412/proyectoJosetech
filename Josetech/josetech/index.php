<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/estilo_principal.css">
</head>
<body>
    <header>
        <?php include './php/header.php'; ?>
    </header>
        <main>
            <section class="carousel-wrap">
                <div class="carousel" id="carousel" aria-roledescription="carousel">
                    <div class="carousel-track" id="carousel-track">
                        
                        <div class="carousel-slide" data-index="0">
                            <img src="./img/carousel1.png" alt="Equipos informáticos y consolas">
                        </div>
                        
                        <div class="carousel-slide" data-index="1">
                            <img src="./img/carousel2.png" alt="Herramientas de reparación">
                        </div>
                        
                        <div class="carousel-slide" data-index="2">
                            <img src="./img/carousel3.png" alt="Proveedores y marcas">
                        </div>
                        
                    </div>

                    <button class="carousel-btn prev" id="carousel-prev" aria-label="Anterior">&#8249;</button>
                    <button class="carousel-btn next" id="carousel-next" aria-label="Siguiente">&#8250;</button>

                    <div class="carousel-indicators" id="carousel-indicators" role="tablist" aria-label="Indicadores del carrusel">
                        <button class="indicator" data-slide-to="0" aria-label="Ir a la diapositiva 1" role="tab" aria-selected="true"></button>
                        <button class="indicator" data-slide-to="1" aria-label="Ir a la diapositiva 2" role="tab" aria-selected="false"></button>
                        <button class="indicator" data-slide-to="2" aria-label="Ir a la diapositiva 3" role="tab" aria-selected="false"></button>
                    </div>
                </div>
            </section>
            <h1>Reparamos iPhone y otros modelos</h1> 
        </main>
    
    <script src="./js/carousel.js" defer></script>
</body>
</html>