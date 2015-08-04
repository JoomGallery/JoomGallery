/****************************************************************************************\
**   JoomGallery 3                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2015  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

(function ($) {
  $.QuickEditingData = function (options) {
    ops = $.extend({
      url: '#',
      getContentCallback: function() {}
    }, options);

    // Toggle visibility of editing units
    $('.jg-show-editing-units a, .jg-cancel').click(function () {
      if($(this).hasClass('jg-icon-disabled')) {
        return false;
      }

      $('.jg-show-editing-units').toggle();
      $('.jg-quick-edit-row, .jg-visible-hidden-toggle, #jg-quick-edit-btn-bar').toggle('slow');

      var id = $(this).attr('data-id');
      if(id) {
        $('#imgtitle_' + id).select();
        $('html, body').animate({
          scrollTop: $('#imgtitle_' + id).closest('tr').prev().offset().top
        }, 2000);
      }

      return false;
    });

    // Don't submit form on key press of 'enter' when editing units are enabled, but send the Ajax request
    $('#userpanelForm').submit(function () {
      if($('#jg-quick-edit-btn-bar').is(':visible')) {
        if(!$('#jg-quick-edit-btn-bar .jg-save').is(':disabled')) {
          $('#jg-quick-edit-btn-bar .jg-save').trigger('click');
        }

        return false;
      }

      return true;
    });

    // Send the Ajax request for updating image data
    $('#jg-quick-edit-btn-bar .jg-save').click(function () {
      var imagesdata = {};
      var submit = true;
      $('.jg-quick-edit-row.changed').each(function () {
        var id = $(this).attr('data-id');
        imagesdata[id] = {
          'imgtitle': $(this).find('#imgtitle_' + id).val(),
          'imgauthor': $(this).find('#imgauthor_' + id).val(),
          'metadesc': $(this).find('#metadesc_' + id).val(),
          'imgtext': ops.getContentCallback('imgtext_' + id)
        };
        if(!imagesdata[id].imgtitle) {
          alert(Joomla.JText._('COM_JOOMGALLERY_COMMON_ALERT_IMAGE_MUST_HAVE_TITLE'));
          $(this).find('#imgtitle_' + id).focus();
          submit = false;

          return false;
        }
      });

      if(!submit) {
        return;
      }

      $.getJSON(ops.url, {images: imagesdata})
        .done(function (response) {
          if (response.success === true) {
            Joomla.renderMessages({'success' : [Joomla.JText._('COM_JOOMGALLERY_USERPANEL_DATACHANGED_SUCCESS')]});
            $('.jg-show-editing-units').toggle();
            $('.jg-quick-edit-row, .jg-visible-hidden-toggle, #jg-quick-edit-btn-bar').toggle('slow');
            $('.jg-quick-edit-row.changed').each(function () {
              var id = $(this).attr('data-id');
              var image_title = $(this).find("input[name='imgtitle']").val();
              $('#jg-title-row-' + id + ' .jg-image-title').html(image_title);
              $(this).removeClass('changed');
            });
            $('#jg-quick-edit-btn-bar .jg-save').attr('disabled', 'disabled');
          } else {
            alert(response.message);
          }
        })
        .fail(function(jqxhr, textStatus, error) {
          alert(textStatus + ': ' + error);
        });

        return false;
    });

    // Mark image as edited
    $('.jg-quick-edit-row input, .jg-quick-edit-row textarea').keyup(function () {
      $(this).closest('.jg-quick-edit-row').addClass('changed');
      $('#jg-quick-edit-btn-bar .jg-save').removeAttr('disabled');
    });

    // Delay adding the 'image marked' detection event for editor iframes so that they have time to load
    setTimeout(function() {
      $('.jg-quick-edit-row').each(function () {
        var id = $(this).attr('data-id');
        $(this).find('iframe').contents().keyup(function () {
          $('.jg-quick-edit-row[data-id=\'' + id + '\']').addClass('changed');
          $('#jg-quick-edit-btn-bar .jg-save').removeAttr('disabled');
        });
      });
    }, 3000);
  };
})(jQuery);