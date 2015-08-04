(function ($) {
  $.QuickEditingData = function (options) {
    ops = $.extend({
      name_editor: 'none',
      url: '#'
    }, options);

    $(".blinker-btn .show_editing_unit").click(function () {
      $(".image_data_row").each(function () {
        var id = $(this).attr("id");
        $('#title_row_' + id).insertBefore($('#' + id));
      });
      $('.image_data_row').toggle("slow");
      $(".hidden-if-edit").css('display', 'none');
      $(".blinker-btn .show_editing_unit").hide();
      $(".blinker-btn .hide_editing_unit").show();
    });

    $(".blinker-btn .hide_editing_unit").click(function () {
      $('.image_data_row').toggle("slow");
      //$(".image_data_row").each(function () {
        var id = $(this).attr("id");
        $('.title_row').appendTo("#imageList tbody");
      //});
      $(".hidden-if-edit").removeAttr("style");
      $(".blinker-btn .save_edited_data").attr("disabled", "disabled");
      $(".blinker-btn .hide_editing_unit").hide();
      $(".blinker-btn .show_editing_unit").show();
    });

    function ifAjaxSuccessful() {
      $(".image_data_row.changed").each(function () {
        var id = $(this).attr('id');
        var image_title = $(this).find("input[name='imgtitle']").val();
        $('#title_row_'+id+' .image_title').html(image_title);
        $(this).removeClass("changed");
        $(".blinker-btn .save_edited_data").attr("disabled", "disabled");
      });
    }

    $(".blinker-btn .save_edited_data").click(function () {
      var imagesdata = {};
      $(".image_data_row.changed").each(function () {
        var id = $(this).attr('id');

        switch (ops.name_editor) {
          case 'tinymce':
          case 'jce':
            var FRAM = document.getElementById("imgtext_" + id + "_ifr");
            var imgtext = FRAM.contentDocument.body.innerHTML;
            break
          default:
            var imgtext = $(this).find("#imgtext_" + id).val();
        }
        imagesdata[id] = {
          "imgtitle": $(this).find('#imgtitle_' + id).val(),
          "imgauthor": $(this).find('#imgauthor_' + id).val(),
          "metadesc": $(this).find('#metadesc_' + id).val(),
          "imgtext": imgtext
        };
      });

      $.ajax({
        url: ops.url,
        type: "POST",
        data: {
          ajaxeditimg: "edit",
          jsonData: JSON.stringify(imagesdata)
        },
        success: function (msg) {
          var data = jQuery.parseJSON(msg).data;
          if (data === true) {
            $('.blinker-msg').css('display', 'block').delay(1000).fadeOut(2000);
            ifAjaxSuccessful();
          } else {
            alert(data);
          }
        },
        response: 'text',
        dataType: 'text'
      });
    });

    $(".image_data_row input").keyup(function () {
      $(this).closest(".image_data_row").addClass("changed");
      $(".blinker-btn .save_edited_data").removeAttr('disabled');
    });

    switch (ops.name_editor) {
      case 'tinymce':
      case 'jce':
        $(".image_data_row").each(function () {
          var id = $(this).attr("id");
          $('#' + id + ' iframe').contents().keyup(function () {
            $('#' + id).addClass("changed");
            $(".blinker-btn .save_edited_data").removeAttr('disabled');
          });
        });
        break
      default:
        $(".image_data_row textarea").keyup(function () {
          $(this).closest(".image_data_row").addClass("changed");
          $(".blinker-btn .save_edited_data").removeAttr('disabled');
        });
    }
  };
})(jQuery);