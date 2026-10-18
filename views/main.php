<?php

use Controllers\GalleryController;

$galleries = GalleryController::getAll();


?>

<div class="view flex-col">
    <div class="flex justify-center">
        <h3 class="flex items-center flex-col text-4xl mb-16 font-bold uppercase tracking-wider">
            Seznam galerií
            <div class="h-1 w-1/2 max-w-32 min-w-16 mt-6 bg-main-400"></div>
        </h3>
    </div>

    <div class="flex flex-col gap-6">
        <?php foreach($galleries as $gallery) { ?>
        <div class="gallery-tab-wrapper relative overflow-hidden bg-back-100">
            <div class="gallery-tab">
                <h3 class="text-2xl font-bold uppercase tracking-wider"><?php echo $gallery->name ?></h3>
                <div class="flex flex-row justify-start items-center">
                    <a class="button-pm" href="<?php echo '?page=gallery&id=' . $gallery->id ?>">
                        <img src="public/assets/icons/picture-white.svg" alt="">
                        Otevřít galerii
                    </a>
                </div>
            </div>
            <div class="absolute left-0 w-full h-full flex flex-row items-center z-20">
                <!--<div class="absolute left-0 h-full w-full z-10 bg-gradient-to-l from-transparent to-main-700 to-75%"></div>-->
                <div class="absolute left-0 h-full w-full z-10  bg-gradient-to-l from-transparent to-back-100 to-75%""></div>
                <img class="absolute left-0 w-full opacity-25" src="public/images/gallery.jpg" alt="">
            </div>
        </div>
        <?php } ?>
    </div>
</div>

    <h2>Test přístupu ke kameře</h2>
    <pre id="result"></pre>

    <button id="camera">Vyzkoušet kameru</button>
    <video id="video" autoplay playsinline style="width:100%"></video>

    <script>
        const result = document.getElementById('result');

        result.textContent = [
            `URL: ${location.href}`,
            `Secure context: ${window.isSecureContext}`,
            `mediaDevices: ${typeof navigator.mediaDevices}`,
            `getUserMedia: ${typeof navigator.mediaDevices?.getUserMedia}`,
            `Prohlížeč: ${navigator.userAgent}`
        ].join('\n');

        document.getElementById('camera').addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' },
                    audio: false
                });

                document.getElementById('video').srcObject = stream;
                result.textContent += '\n\nKamera funguje!';
            } catch (error) {
                result.textContent += `\n\nChyba: ${error.name}: ${error.message}`;
            }
        });
    </script>