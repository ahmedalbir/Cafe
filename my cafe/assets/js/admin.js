document.querySelectorAll('[data-preview-input]').forEach((input) => {
  input.addEventListener('change', () => {
    const preview = document.querySelector(`[data-preview-image="${input.dataset.previewInput}"]`);
    const file = input.files && input.files[0];
    if (!preview || !file) {
      return;
    }
    const reader = new FileReader();
    reader.onload = () => {
      preview.src = String(reader.result || preview.src);
    };
    reader.readAsDataURL(file);
  });
});
