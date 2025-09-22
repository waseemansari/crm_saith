  function get_data_stock_summary_report() {
    "use strict";
    var check_csrf_protection = $('input[name="check_csrf_protection"]').val();
    var formData = new FormData();
    if(check_csrf_protection == 'true' || check_csrf_protection == true){
      formData.append(csrfData.token_name, csrfData.hash);
    }
          formData.append("from_date", $('input[name="from_date"]').val());
          formData.append("to_date", $('input[name="to_date"]').val());
          formData.append("warehouse_id", $('select[id="warehouse_filter"]').val());
          formData.append("commodity_id", $('select[id="commodity_filter"]').val());
      $.ajax({ 
            url: admin_url + 'warehouse/get_data_stock_summary_report', 
            method: 'post', 
            data: formData, 
            contentType: false, 
            processData: false
        }).done(function(response) {
          var response = JSON.parse(response);

          $('#stock_s_report').html('');
          $('#stock_s_report').append(response.value);
         
        });

      
}

function stock_submit(invoker){
  "use strict";
      $('#print_report').submit(); 
}

function stock_summary_report_export_excel(){
  "use strict";
  var ids = [];
  var data = {};

  data.from_date = $('input[name="from_date"]').val();
  data.to_date = $('input[name="to_date"]').val();
  data.warehouse_filter = $('select[id="warehouse_filter"]').val();
  data.commodity_filter = $('select[id="commodity_filter"]').val();

  $(event).addClass('disabled');
  setTimeout(function() {
    $.post(admin_url + 'warehouse/stock_summary_report_export_excel', data).done(function(response) {
      response = JSON.parse(response);
      if(response.success == true){
        alert_float('success', response.messages);

        $('#dowload_items').removeClass('hide');

        $('#dowload_items').attr({target: '_blank', 
         href  : site_url +response.filename});

      }else{
        alert_float('success', response.messages);

      }

    }).fail(function(data) {


    });
  }, 200);
}