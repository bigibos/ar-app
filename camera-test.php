<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test kamery</title>
</head>
<body>
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
</body>
</html>