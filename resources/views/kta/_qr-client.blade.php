<script src="{{ asset('js/pergabi-qr.js') }}?v={{ @filemtime(public_path('js/pergabi-qr.js')) ?: '1' }}"></script>
<script>
    window.whenPergabiQrReady = function (callback) {
        if (window.PergabiQr) {
            callback();
            return;
        }

        var tries = 0;
        var timer = setInterval(function () {
            tries += 1;
            if (window.PergabiQr || tries > 40) {
                clearInterval(timer);
                if (window.PergabiQr) {
                    callback();
                }
            }
        }, 50);
    };

    window.drawPergabiQr = function (canvas, payload, size) {
        if (! canvas || ! payload || ! window.PergabiQr) {
            return Promise.resolve();
        }

        window.PergabiQr.toCanvas(canvas, payload, size || canvas.width || 160);
        return Promise.resolve();
    };

    window.drawPergabiQrAll = function (selector, payload, size) {
        return new Promise(function (resolve) {
            window.whenPergabiQrReady(function () {
                document.querySelectorAll(selector).forEach(function (canvas) {
                    if (canvas.hasAttribute('data-kta-pengesahan')) {
                        return;
                    }
                    window.drawPergabiQr(canvas, payload, size);
                });
                resolve();
            });
        });
    };

    window.drawPergabiQrMap = function (selector, payloads) {
        return new Promise(function (resolve) {
            window.whenPergabiQrReady(function () {
                document.querySelectorAll(selector).forEach(function (canvas) {
                    var key = canvas.getAttribute('data-kta-pengesahan');
                    window.drawPergabiQr(canvas, payloads && payloads[key], canvas.width || 160);
                });
                resolve();
            });
        });
    };
</script>
