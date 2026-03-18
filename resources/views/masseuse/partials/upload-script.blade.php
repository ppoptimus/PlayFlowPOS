<script>
    (function () {
        const MAX_UPLOAD_BYTES = 1600 * 1024;
        const MAX_DIMENSION = 1600;

        function formatBytes(bytes) {
            if (!Number.isFinite(bytes) || bytes <= 0) return '0 KB';
            if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
            return Math.round(bytes / 1024) + ' KB';
        }

        function setUploadNote(targetKey, message, isError) {
            const noteEl = document.querySelector('[data-upload-note="' + targetKey + '"]');
            if (!noteEl) return;
            noteEl.textContent = message;
            noteEl.classList.toggle('is-error', Boolean(isError));
        }

        function setPreview(targetKey, file) {
            const previewEl = document.querySelector('[data-image-preview="' + targetKey + '"]');
            const pickerEl = document.querySelector('[data-upload-picker="' + targetKey + '"]');
            if (!previewEl || !file) return;

            const reader = new FileReader();
            reader.onload = function (event) {
                previewEl.src = String((event.target && event.target.result) || previewEl.src);
                if (pickerEl) {
                    pickerEl.classList.add('has-image');
                }
            };
            reader.readAsDataURL(file);
        }

        function loadImage(file) {
            return new Promise(function (resolve, reject) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    const image = new Image();
                    image.onload = function () { resolve(image); };
                    image.onerror = reject;
                    image.src = String((event.target && event.target.result) || '');
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        function canvasToJpegBlob(canvas, quality) {
            return new Promise(function (resolve, reject) {
                if (!canvas.toBlob) {
                    reject(new Error('Canvas toBlob is not supported'));
                    return;
                }

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        reject(new Error('Unable to create compressed blob'));
                        return;
                    }

                    resolve(blob);
                }, 'image/jpeg', quality);
            });
        }

        async function compressImage(file) {
            const image = await loadImage(file);

            let width = image.naturalWidth || image.width;
            let height = image.naturalHeight || image.height;
            const maxSide = Math.max(width, height);
            const scale = maxSide > MAX_DIMENSION ? (MAX_DIMENSION / maxSide) : 1;

            width = Math.max(1, Math.round(width * scale));
            height = Math.max(1, Math.round(height * scale));

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            if (!context) {
                throw new Error('Canvas context unavailable');
            }

            canvas.width = width;
            canvas.height = height;

            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, width, height);
            context.drawImage(image, 0, 0, width, height);

            let quality = 0.86;
            let blob = await canvasToJpegBlob(canvas, quality);

            while (blob.size > MAX_UPLOAD_BYTES && quality > 0.46) {
                quality -= 0.08;
                blob = await canvasToJpegBlob(canvas, quality);
            }

            if (blob.size > MAX_UPLOAD_BYTES) {
                let nextWidth = width;
                let nextHeight = height;

                while (blob.size > MAX_UPLOAD_BYTES && nextWidth > 480 && nextHeight > 480) {
                    nextWidth = Math.max(480, Math.round(nextWidth * 0.86));
                    nextHeight = Math.max(480, Math.round(nextHeight * 0.86));
                    canvas.width = nextWidth;
                    canvas.height = nextHeight;
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, nextWidth, nextHeight);
                    context.drawImage(image, 0, 0, nextWidth, nextHeight);
                    blob = await canvasToJpegBlob(canvas, Math.max(quality, 0.46));
                }
            }

            return new File(
                [blob],
                (file.name || 'profile').replace(/\.[^.]+$/, '') + '.jpg',
                { type: 'image/jpeg', lastModified: Date.now() }
            );
        }

        async function handleFileInput(input) {
            const targetKey = String(input.getAttribute('data-preview-target') || '');
            const file = input.files && input.files[0] ? input.files[0] : null;

            if (!targetKey || !file) {
                return;
            }

            setUploadNote(targetKey, 'กำลังเตรียมรูปภาพ...', false);

            try {
                let finalFile = file;

                if (file.size > MAX_UPLOAD_BYTES) {
                    finalFile = await compressImage(file);

                    if (typeof DataTransfer !== 'undefined') {
                        const transfer = new DataTransfer();
                        transfer.items.add(finalFile);
                        input.files = transfer.files;
                    }
                }

                setPreview(targetKey, finalFile);
                setUploadNote(
                    targetKey,
                    file.size > finalFile.size
                        ? 'ย่อรูปแล้วจาก ' + formatBytes(file.size) + ' เหลือ ' + formatBytes(finalFile.size)
                        : 'ไฟล์พร้อมอัปโหลด ขนาด ' + formatBytes(finalFile.size),
                    finalFile.size > MAX_UPLOAD_BYTES
                );

                if (finalFile.size > MAX_UPLOAD_BYTES) {
                    setUploadNote(
                        targetKey,
                        'ไฟล์ยังใหญ่เกินไปสำหรับระบบ กรุณาเลือกรูปขนาดเล็กลงอีกเล็กน้อย',
                        true
                    );
                }
            } catch (error) {
                setPreview(targetKey, file);
                setUploadNote(
                    targetKey,
                    'browser นี้ย่อรูปอัตโนมัติไม่ได้ กรุณาใช้ไฟล์ไม่เกิน 2 MB',
                    true
                );
            }
        }

        document.querySelectorAll('input[type="file"][data-compress-image="true"]').forEach(function (input) {
            input.addEventListener('change', function () {
                handleFileInput(input);
            });
        });
    })();
</script>
