<div class="container">
    <h1>GalleryController/index</h1>
    <div class="box">

        <?php $this->renderFeedbackMessages(); ?>

        <form class="gallery-upload" method="post" enctype="multipart/form-data" action="<?= Config::get('URL'); ?>gallery/upload">
            <label class="gallery-file-label" for="gallery-picture">
                Choose file
            </label>
            <input id="gallery-picture" type="file" name="picture" accept=".jpg,.jpeg,.png,.gif" required>
            <span id="gallery-file-name" class="gallery-file-name">No file selected</span>
            <button type="submit">Upload</button>
        </form>

        <div class="gallery-grid">
            <?php foreach ($this->pictures as $picture) { ?>
                <div class="gallery-item">
                    <div class="gallery-image-box">
                        <img src="<?= Config::get('URL') . 'gallery/showImage/' . (int) $picture->id; ?>" alt="">
                    </div>
                    <div class="gallery-actions">
                        <a href="<?= Config::get('URL') . 'gallery/download/' . (int) $picture->id; ?>">Download</a>
                        <a href="<?= Config::get('URL') . 'gallery/delete/' . (int) $picture->id; ?>" onclick="return confirm('Really delete this picture?');">Delete</a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('gallery-picture').addEventListener('change', function () {
        document.getElementById('gallery-file-name').textContent = this.files.length ? this.files[0].name : 'No file selected';
    });
</script>
