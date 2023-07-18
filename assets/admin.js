jQuery(document).ready(function($) {
    var mediaUploader;
    var addButton = $('.add-image');
    var imagesContainer = $('.slider-images-container');

    addButton.on('click', function(e) {
      e.preventDefault();

      if (mediaUploader) {
        mediaUploader.open();
        return;
      }

      mediaUploader = wp.media({
        title: 'Add Images to Slider',
        button: {
          text: 'Add Images'
        },
        multiple: true
      });

      mediaUploader.on('select', function() {
        var attachments = mediaUploader.state().get('selection').toJSON();

        attachments.forEach(function(attachment) {
          imagesContainer.append('<div class="slider-image"><img src="' + attachment.sizes.thumbnail.url + '" width="' + attachment.sizes.thumbnail.width + '" height="' + attachment.sizes.thumbnail.height + '" /><a href="#" class="remove-image">Remove</a><input type="hidden" name="slider_images[]" value="' + attachment.id + '" /></div>');
        });
      });

      mediaUploader.open();
    });

    imagesContainer.on('click', '.remove-image', function(e) {
      e.preventDefault();
      $(this).closest('.slider-image').remove();
    });
  });