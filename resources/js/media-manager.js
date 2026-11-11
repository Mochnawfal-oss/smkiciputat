const formatBytes = (bytes) => {
    if (!Number.isFinite(bytes) || bytes < 0) return '—';
    if (bytes < 1024) return `${bytes} B`;

    const units = ['KB', 'MB', 'GB', 'TB'];
    let value = bytes;
    let unitIndex = -1;

    do {
        value /= 1024;
        unitIndex += 1;
    } while (value >= 1024 && unitIndex < units.length - 1);

    return `${value.toFixed(1)} ${units[unitIndex]}`;
};

const generatedVideoPosters = new WeakMap();

const setFileInput = (input, file) => {
    const transfer = new DataTransfer();
    transfer.items.add(file);
    input.files = transfer.files;
};

const setSourceFile = (input, file) => {
    const form = input.closest('form');
    let sourceInput = form.querySelector(`[data-media-source-for="${CSS.escape(input.id)}"]`);

    if (!sourceInput) {
        sourceInput = document.createElement('input');
        sourceInput.type = 'file';
        sourceInput.hidden = true;
        sourceInput.dataset.mediaSourceFor = input.id;
        sourceInput.name = input.dataset.mediaSourceName || 'source_file';
        form.append(sourceInput);
    }

    setFileInput(sourceInput, file);
    sourceInput.disabled = false;

    return sourceInput;
};

const createVideoPoster = (video) => new Promise((resolve) => {
    if (video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
        resolve(null);
        return;
    }

    const scale = Math.min(1, 1280 / video.videoWidth);
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(video.videoWidth * scale));
    canvas.height = Math.max(1, Math.round(video.videoHeight * scale));
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.toBlob(resolve, 'image/webp', 0.88);
});

const readImage = (file) => new Promise((resolve, reject) => {
    const image = new Image();
    const url = URL.createObjectURL(file);

    image.onload = () => {
        URL.revokeObjectURL(url);
        resolve(image);
    };
    image.onerror = () => {
        URL.revokeObjectURL(url);
        reject(new Error('Foto tidak dapat dibuka di browser ini.'));
    };
    image.src = url;
});

const cropRectangle = (ratioValue, width, height, imageWidth, imageHeight, rotation) => {
    let ratio;

    if (ratioValue === 'original') {
        ratio = (rotation % 180 === 0 ? imageWidth / imageHeight : imageHeight / imageWidth);
    } else {
        const [ratioWidth, ratioHeight] = ratioValue.split(':').map(Number);
        ratio = ratioWidth / ratioHeight;
    }

    let cropWidth = width;
    let cropHeight = cropWidth / ratio;

    if (cropHeight > height) {
        cropHeight = height;
        cropWidth = cropHeight * ratio;
    }

    return {
        x: (width - cropWidth) / 2,
        y: (height - cropHeight) / 2,
        width: cropWidth,
        height: cropHeight,
    };
};

const initializeMediaEditor = () => {
    const dialog = document.querySelector('[data-image-editor]');

    if (!dialog) return;

    const canvas = dialog.querySelector('[data-image-editor-canvas]');
    const context = canvas.getContext('2d');
    const ratioSelect = dialog.querySelector('[data-image-ratio]');
    const zoomControl = dialog.querySelector('[data-image-zoom]');
    const brightnessControl = dialog.querySelector('[data-image-brightness]');
    const contrastControl = dialog.querySelector('[data-image-contrast]');
    const saturationControl = dialog.querySelector('[data-image-saturation]');
    const imageLayer = document.createElement('canvas');
    imageLayer.width = canvas.width;
    imageLayer.height = canvas.height;
    const layerContext = imageLayer.getContext('2d');
    let activeInput = null;
    let sourceFile = null;
    let sourceImage = null;
    let angle = 0;
    let flipX = false;
    let flipY = false;
    let zoom = 1;
    let panX = 0;
    let panY = 0;
    let dragPoint = null;

    const render = () => {
        if (!sourceImage) return;

        const rectangle = cropRectangle(
            ratioSelect.value,
            canvas.width,
            canvas.height,
            sourceImage.naturalWidth,
            sourceImage.naturalHeight,
            angle,
        );
        const rotatedWidth = angle % 180 === 0 ? sourceImage.naturalWidth : sourceImage.naturalHeight;
        const rotatedHeight = angle % 180 === 0 ? sourceImage.naturalHeight : sourceImage.naturalWidth;
        const scale = Math.max(rectangle.width / rotatedWidth, rectangle.height / rotatedHeight) * zoom;

        layerContext.clearRect(0, 0, imageLayer.width, imageLayer.height);
        layerContext.save();
        layerContext.filter = `brightness(${brightnessControl.value}%) contrast(${contrastControl.value}%) saturate(${saturationControl.value}%)`;
        layerContext.translate(canvas.width / 2 + panX, canvas.height / 2 + panY);
        layerContext.rotate(angle * Math.PI / 180);
        layerContext.scale(scale * (flipX ? -1 : 1), scale * (flipY ? -1 : 1));
        layerContext.drawImage(
            sourceImage,
            -sourceImage.naturalWidth / 2,
            -sourceImage.naturalHeight / 2,
        );
        layerContext.restore();

        context.clearRect(0, 0, canvas.width, canvas.height);
        context.drawImage(imageLayer, 0, 0);
        context.fillStyle = 'rgba(13, 30, 21, .58)';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.clearRect(rectangle.x, rectangle.y, rectangle.width, rectangle.height);
        context.drawImage(
            imageLayer,
            rectangle.x,
            rectangle.y,
            rectangle.width,
            rectangle.height,
            rectangle.x,
            rectangle.y,
            rectangle.width,
            rectangle.height,
        );
        context.strokeStyle = '#fff';
        context.lineWidth = 2;
        context.strokeRect(rectangle.x + 1, rectangle.y + 1, rectangle.width - 2, rectangle.height - 2);
    };

    const reset = () => {
        angle = 0;
        flipX = false;
        flipY = false;
        zoom = 1;
        panX = 0;
        panY = 0;
        ratioSelect.value = 'original';
        zoomControl.value = '1';
        brightnessControl.value = '100';
        contrastControl.value = '100';
        saturationControl.value = '100';
        render();
    };

    document.querySelectorAll('[data-media-edit]').forEach((button) => {
        button.addEventListener('click', async () => {
            const input = button.closest('[data-media-field]')?.querySelector('[data-media-managed]')
                || button.closest('form')?.querySelector('[data-media-file]');
            const file = input?.files?.[0];

            if (!input || !file || !file.type.startsWith('image/')) return;

            try {
                activeInput = input;
                const sourceInput = input.closest('form')
                    .querySelector(`[data-media-source-for="${CSS.escape(input.id)}"]`);
                sourceFile = sourceInput?.files?.[0] || file;
                sourceImage = await readImage(sourceFile);
                reset();
                dialog.showModal();
            } catch (error) {
                const status = input.closest('[data-media-field]')?.querySelector('[data-media-status]');
                if (status) status.textContent = error.message;
            }
        });
    });

    [ratioSelect, zoomControl, brightnessControl, contrastControl, saturationControl].forEach((control) => {
        control.addEventListener('input', () => {
            zoom = Number(zoomControl.value);
            render();
        });
    });
    dialog.querySelector('[data-image-rotate]').addEventListener('click', () => {
        angle = (angle + 90) % 360;
        render();
    });
    dialog.querySelector('[data-image-flip-horizontal]').addEventListener('click', () => {
        flipX = !flipX;
        render();
    });
    dialog.querySelector('[data-image-flip-vertical]').addEventListener('click', () => {
        flipY = !flipY;
        render();
    });
    dialog.querySelector('[data-image-reset]').addEventListener('click', reset);
    canvas.addEventListener('pointerdown', (event) => {
        dragPoint = { x: event.clientX, y: event.clientY };
        canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!dragPoint) return;
        const bounds = canvas.getBoundingClientRect();
        panX += (event.clientX - dragPoint.x) * (canvas.width / bounds.width);
        panY += (event.clientY - dragPoint.y) * (canvas.height / bounds.height);
        dragPoint = { x: event.clientX, y: event.clientY };
        render();
    });
    canvas.addEventListener('pointerup', () => {
        dragPoint = null;
    });
    dialog.querySelector('[data-image-apply]').addEventListener('click', () => {
        if (!activeInput || !sourceFile) return;

        const rectangle = cropRectangle(
            ratioSelect.value,
            canvas.width,
            canvas.height,
            sourceImage.naturalWidth,
            sourceImage.naturalHeight,
            angle,
        );
        const output = document.createElement('canvas');
        output.width = Math.max(1, Math.round(rectangle.width * 2));
        output.height = Math.max(1, Math.round(rectangle.height * 2));
        const outputContext = output.getContext('2d');
        outputContext.drawImage(
            imageLayer,
            rectangle.x,
            rectangle.y,
            rectangle.width,
            rectangle.height,
            0,
            0,
            output.width,
            output.height,
        );
        output.toBlob((blob) => {
            if (!blob) {
                const status = activeInput.closest('[data-media-field]')?.querySelector('[data-media-status]');
                if (status) status.textContent = 'Hasil edit tidak dapat dibuat. Coba format atau ukuran lain.';
                return;
            }

            const filename = sourceFile.name.replace(/\.[^.]+$/, '') + '-edited.webp';
            const editedFile = new File([blob], filename, { type: blob.type || 'image/webp' });
            setSourceFile(activeInput, sourceFile);
            setFileInput(activeInput, editedFile);
            activeInput.dataset.mediaEdited = 'true';
            activeInput.dispatchEvent(new Event('change', { bubbles: true }));
            dialog.close();
        }, 'image/webp', 0.9);
    });
    dialog.addEventListener('close', () => {
        activeInput = null;
        sourceFile = null;
        sourceImage = null;
        dragPoint = null;
    });
};

const sendUploadRequest = (method, url, body, token, onProgress, setRequest) => new Promise((resolve, reject) => {
    const request = new XMLHttpRequest();
    request.open(method, url);
    request.setRequestHeader('Accept', 'application/json');
    request.setRequestHeader('X-CSRF-TOKEN', token);
    if (!(body instanceof FormData)) request.setRequestHeader('Content-Type', 'application/json');
    if (onProgress) {
        request.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) onProgress(event.loaded, event.total);
        });
    }
    request.addEventListener('load', () => {
        let response = {};
        try {
            response = request.responseText ? JSON.parse(request.responseText) : {};
        } catch {
            reject(new Error('Server memberikan respons upload yang tidak dapat dibaca.'));
            return;
        }

        if (request.status >= 200 && request.status < 300) {
            resolve(response);
            return;
        }

        const firstError = Object.values(response.errors || {}).flat()[0];
        reject(new Error(firstError || response.message || 'Unggahan gagal. Silakan coba kembali.'));
    });
    request.addEventListener('error', () => reject(new Error('Koneksi terputus saat mengunggah media.')));
    request.addEventListener('abort', () => reject(new Error('Unggahan dibatalkan.')));
    setRequest(request);
    request.send(body instanceof FormData ? body : JSON.stringify(body));
});

const uploadFile = async (formData, form, progress, onCancel) => {
    const file = formData.get('file');
    const sourceFile = formData.get('source_file');
    if (!(file instanceof File)) throw new Error('Pilih file media terlebih dahulu.');

    const uploadBase = form.dataset.mediaUploadBase
        || form.querySelector('[data-media-field]')?.dataset.mediaUploadBase;
    if (!uploadBase) throw new Error('Endpoint unggah media tidak tersedia.');
    const token = form.querySelector('input[name="_token"]')?.value || '';
    const category = formData.get('category') || 'general';
    const quality = formData.get('quality') || 'optimized';
    const slots = [{ key: 'file', file }];
    if (sourceFile instanceof File) slots.push({ key: 'source_file', file: sourceFile });

    const fingerprint = slots.map(({ file: slotFile }) => (
        `${slotFile.name}|${slotFile.size}|${slotFile.lastModified}|${slotFile.type}`
    )).join('::') + `|${category}|${quality}|${formData.get('replace_asset_id') || ''}|${formData.get('upload_scope') || ''}`;
    const storageKey = `smki-media-upload:${fingerprint}`;
    let uploadId = null;
    let activeRequest = null;
    let cancelled = false;
    const cancel = () => {
        cancelled = true;
        activeRequest?.abort();
        if (uploadId) {
            fetch(`${uploadBase}/${uploadId}`, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
            }).catch(() => {});
        }
        window.localStorage.removeItem(storageKey);
    };
    onCancel(cancel);

    const request = (method, url, body, onProgress) => sendUploadRequest(
        method,
        url,
        body,
        token,
        onProgress,
        (active) => { activeRequest = active; },
    );
    const fileMetadata = {
        filename: file.name,
        mime_type: file.type,
        size: file.size,
        media_type: file.type.startsWith('video/') ? 'video' : 'image',
        category,
        quality,
        duration_seconds: formData.get('duration_seconds') || null,
        width: formData.get('width') || null,
        height: formData.get('height') || null,
        alt_text: formData.get('alt_text') || null,
        replace_asset_id: formData.get('replace_asset_id') || null,
    };

    if (slots.length > 1) {
        fileMetadata.source_filename = sourceFile.name;
        fileMetadata.source_mime_type = sourceFile.type;
        fileMetadata.source_size = sourceFile.size;
    }

    const savedId = window.localStorage.getItem(storageKey);
    let status = null;
    if (savedId) {
        try {
            status = await request('GET', `${uploadBase}/${savedId}`, null);
            uploadId = savedId;
        } catch {
            window.localStorage.removeItem(storageKey);
        }
    }

    if (!status || slots.some(({ key, file: slotFile }) => (
        status.slots?.[key]?.filename !== slotFile.name || status.slots?.[key]?.size !== slotFile.size
    ))) {
        status = await request('POST', uploadBase, fileMetadata);
        uploadId = status.id;
        window.localStorage.setItem(storageKey, uploadId);
    }

    const totalBytes = slots.reduce((sum, slot) => sum + slot.file.size, 0);
    let completedBytes = 0;

    for (const slot of slots) {
        const received = new Set(status.slots?.[slot.key]?.received || []);
        const count = Math.ceil(slot.file.size / status.chunk_size);

        for (let index = 0; index < count; index += 1) {
            if (cancelled) throw new Error('Unggahan dibatalkan.');
            const start = index * status.chunk_size;
            const end = Math.min(slot.file.size, start + status.chunk_size);
            const chunkSize = end - start;

            if (received.has(index)) {
                completedBytes += chunkSize;
                progress(completedBytes / totalBytes, `Melanjutkan bagian ${index + 1} dari ${count}`);
                continue;
            }

            const chunk = new FormData();
            chunk.append('chunk', slot.file.slice(start, end), `${slot.file.name}.part`);
            await request(
                'POST',
                `${uploadBase}/${uploadId}/chunks/${slot.key}/${index}`,
                chunk,
                (loaded) => progress((completedBytes + loaded) / totalBytes, `Mengunggah bagian ${index + 1} dari ${count}`),
            );
            completedBytes += chunkSize;
            progress(completedBytes / totalBytes, 'Bagian tersimpan');
        }
    }

    if (cancelled) throw new Error('Unggahan dibatalkan.');
    const asset = await request('POST', `${uploadBase}/${uploadId}/complete`, {});
    const mediaInput = form.querySelector('[data-media-file]');
    const generatedPoster = mediaInput ? generatedVideoPosters.get(mediaInput) : null;
    if (asset.type === 'video' && generatedPoster && asset.poster_url) {
        const posterData = new FormData();
        posterData.append('poster', generatedPoster, 'poster.webp');
        try {
            await request('POST', asset.poster_url, posterData);
            generatedVideoPosters.delete(mediaInput);
        } catch (error) {
            asset.poster_error = error.message;
        }
    }
    window.localStorage.removeItem(storageKey);

    return asset;
};

const initializeMediaInputs = () => {
    document.querySelectorAll('[data-media-managed], [data-media-file]').forEach((input) => {
        const form = input.closest('form');
        const wrapper = input.closest('[data-media-field]') || form;
        const preview = wrapper.querySelector('[data-media-preview]');
        const imagePreview = wrapper.querySelector('[data-media-image-preview]')
            || (input.dataset.mediaPreview ? wrapper.querySelector(input.dataset.mediaPreview) : null);
        const videoPreview = wrapper.querySelector('[data-media-video-preview]');
        const details = wrapper.querySelector('[data-media-file-details]')
            || (input.dataset.mediaDetails ? wrapper.querySelector(input.dataset.mediaDetails) : null);
        const editButton = wrapper.querySelector('[data-media-edit]');
        const assetInput = wrapper.querySelector('[data-media-asset]');
        const status = wrapper.querySelector('[data-media-status]');
        const objectUrl = { value: null };

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;

            if (assetInput) assetInput.value = '';
            if (input.dataset.mediaEdited !== 'true') {
                const oldSource = form.querySelector(`[data-media-source-for="${CSS.escape(input.id)}"]`);
                if (oldSource) {
                    oldSource.value = '';
                    oldSource.disabled = true;
                }
            } else {
                input.dataset.mediaEdited = 'false';
            }

            if (objectUrl.value) URL.revokeObjectURL(objectUrl.value);
            objectUrl.value = URL.createObjectURL(file);
            if (preview) preview.hidden = false;
            if (details) details.textContent = `${file.name} · ${formatBytes(file.size)}`;
            if (status) status.textContent = '';
            if (editButton) editButton.disabled = !file.type.startsWith('image/');

            if (file.type.startsWith('image/') && imagePreview) {
                imagePreview.src = objectUrl.value;
                imagePreview.hidden = false;
                const image = new Image();
                image.onload = () => {
                    if (details) details.textContent = `${file.name} · ${image.naturalWidth} × ${image.naturalHeight} · ${formatBytes(file.size)}`;
                };
                image.src = objectUrl.value;
            } else if (file.type.startsWith('video/') && videoPreview) {
                videoPreview.src = objectUrl.value;
                videoPreview.hidden = false;
                videoPreview.onloadedmetadata = () => {
                    const duration = Math.round(videoPreview.duration);
                    const widthField = form.querySelector('[data-media-width]');
                    const heightField = form.querySelector('[data-media-height]');
                    const durationField = form.querySelector('[data-media-duration]');
                    if (widthField) widthField.value = String(videoPreview.videoWidth);
                    if (heightField) heightField.value = String(videoPreview.videoHeight);
                    if (durationField) durationField.value = String(duration);
                    if (details) details.textContent = `${file.name} · ${videoPreview.videoWidth} × ${videoPreview.videoHeight} · ${duration}s · ${formatBytes(file.size)}`;
                    const capture = async () => {
                        const poster = await createVideoPoster(videoPreview);
                        if (poster) generatedVideoPosters.set(input, poster);
                    };
                    videoPreview.addEventListener('seeked', capture, { once: true });
                    videoPreview.currentTime = Math.min(1, videoPreview.duration / 2);
                    window.setTimeout(capture, 900);
                };
            } else if (imagePreview) {
                imagePreview.hidden = true;
            }
        });
    });

    document.querySelectorAll('[data-media-upload-form]').forEach((form) => {
        const input = form.querySelector('[data-media-file]');
        const progress = form.querySelector('[data-media-progress]');
        const progressBar = form.querySelector('[data-media-progress-bar]');
        const progressValue = form.querySelector('[data-media-progress-value]');
        const progressStatus = form.querySelector('[data-media-progress-status]');
        const status = form.querySelector('[data-media-status]');
        const submit = form.querySelector('[data-media-submit]');
        let cancelUpload = null;

        form.addEventListener('submit', async (event) => {
            if (!input?.files?.[0] || form.dataset.mediaSubmitted === 'true') return;

            event.preventDefault();
            progress.hidden = false;
            submit.disabled = true;
            status.textContent = '';
            const data = new FormData(form);
            data.set('upload_scope', input.id || 'media-upload');

            try {
                const asset = await uploadFile(data, form, (fraction, message) => {
                    const percent = Math.round(fraction * 100);
                    progressBar.value = percent;
                    progressValue.textContent = `${percent}%`;
                    progressStatus.textContent = message || `Mengunggah ${input.files[0].name}`;
                }, (cancel) => { cancelUpload = cancel; });
                progressStatus.textContent = asset.poster_error
                    ? 'Media tersimpan, tetapi thumbnail otomatis gagal dibuat.'
                    : asset.replacement_cleanup_pending
                    ? 'Penggantian selesai; pembersihan file lama tertunda.'
                    : 'Selesai';
                status.textContent = asset.poster_error
                    ? `Media berhasil diunggah, tetapi thumbnail otomatis gagal: ${asset.poster_error}`
                    : asset.replacement_cleanup_pending
                    ? 'Media diganti. File lama masih tersimpan dan dapat dihapus setelah diperiksa.'
                    : 'Media berhasil diunggah.';
                form.dataset.mediaSubmitted = 'true';
                window.setTimeout(
                    () => window.location.reload(),
                    asset.poster_error || asset.replacement_cleanup_pending ? 1800 : 300,
                );
                return;
            } catch (error) {
                status.textContent = error.message;
                submit.disabled = false;
            } finally {
                cancelUpload = null;
            }
        });

        form.querySelector('[data-media-cancel]')?.addEventListener('click', () => cancelUpload?.());
    });

    document.querySelectorAll('form').forEach((form) => {
        const inputs = [...form.querySelectorAll('[data-media-managed]')];
        if (inputs.length === 0) return;

        const uploadUrl = inputs[0].closest('[data-media-field]')?.dataset.mediaUploadUrl;
        if (!uploadUrl) return;

        let cancelUpload = null;
        form.addEventListener('submit', async (event) => {
            const pendingInputs = inputs.filter((input) => input.files?.length && !input.disabled);
            if (pendingInputs.length === 0 || form.dataset.mediaUploadComplete === 'true') return;

            event.preventDefault();
            const submitter = event.submitter;

            try {
                for (const input of pendingInputs) {
                    const wrapper = input.closest('[data-media-field]');
                    const progress = wrapper.querySelector('[data-media-progress]');
                    const progressBar = wrapper.querySelector('[data-media-progress-bar]');
                    const progressValue = wrapper.querySelector('[data-media-progress-value]');
                    const progressStatus = wrapper.querySelector('[data-media-progress-status]');
                    const status = wrapper.querySelector('[data-media-status]');
                    const sourceInput = form.querySelector(`[data-media-source-for="${CSS.escape(input.id)}"]`);
                    const file = input.files[0];
                    const data = new FormData();
                    data.append('file', file, file.name);
                    data.append('category', wrapper.dataset.mediaCategory || 'general');
                    data.append('quality', wrapper.querySelector('[data-media-quality]')?.value || 'optimized');
                    data.append('upload_scope', input.id);
                    const sourceFile = sourceInput?.files?.[0];
                    if (sourceFile) data.append('source_file', sourceFile, sourceFile.name);

                    progress.hidden = false;
                    status.textContent = '';
                    const asset = await uploadFile(data, form, (fraction, message) => {
                        const percent = Math.round(fraction * 100);
                        progressBar.value = percent;
                        progressValue.textContent = `${percent}%`;
                        progressStatus.textContent = message || `Mengunggah ${file.name}`;
                    }, (cancel) => { cancelUpload = cancel; });

                    const assetId = wrapper.querySelector('[data-media-asset]');
                    if (assetId) assetId.value = String(asset.id);
                    input.disabled = true;
                    if (sourceInput) sourceInput.disabled = true;
                    progressStatus.textContent = 'Selesai';
                }

                form.dataset.mediaUploadComplete = 'true';
                if (submitter && !submitter.disabled) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
            } catch (error) {
                const activeWrapper = pendingInputs.find((input) => !input.disabled)?.closest('[data-media-field]');
                const status = activeWrapper?.querySelector('[data-media-status]');
                if (status) status.textContent = error.message;
                if (submitter) submitter.disabled = false;
            } finally {
                cancelUpload = null;
            }
        });

        form.querySelectorAll('[data-media-cancel]').forEach((button) => {
            button.addEventListener('click', () => cancelUpload?.());
        });
    });

    document.querySelectorAll('[data-media-capture-poster]').forEach((button) => {
        button.addEventListener('click', () => {
            const video = button.closest('.admin-media-card')?.querySelector('[data-media-video-card]');
            if (!video || video.readyState < 2) {
                window.alert('Putar atau geser video ke frame yang ingin digunakan terlebih dahulu.');
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => {
                if (!blob) {
                    window.alert('Thumbnail tidak dapat dibuat dari frame ini.');
                    return;
                }

                const data = new FormData();
                data.append('poster', blob, 'poster.webp');
                const token = document.querySelector('input[name="_token"]')?.value;
                const request = new XMLHttpRequest();
                request.open('POST', button.dataset.url);
                request.setRequestHeader('Accept', 'application/json');
                request.setRequestHeader('X-CSRF-TOKEN', token || '');
                data.append('_method', 'PUT');
                request.addEventListener('load', () => {
                    if (request.status < 200 || request.status >= 300) {
                        window.alert('Thumbnail gagal disimpan.');
                        return;
                    }

                    const result = JSON.parse(request.responseText);
                    video.poster = result.poster;
                });
                request.addEventListener('error', () => window.alert('Koneksi terputus saat menyimpan thumbnail.'));
                request.send(data);
            }, 'image/webp', 0.9);
        });
    });
};

const initializeMedia = () => {
    initializeMediaEditor();
    initializeMediaInputs();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMedia, { once: true });
} else {
    initializeMedia();
}
