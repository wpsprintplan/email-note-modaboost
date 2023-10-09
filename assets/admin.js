$ = jQuery;
$(document).ready (function () {  
    var table = $('#email-main-tab').DataTable({
        pageLength : 100,
    });

    $('#checkall').click(function(){
        if($(this).is(':checked')){
            $('.delete_check').prop('checked', true);
        }else{
            $('.delete_check').prop('checked', false);
        }
    });

    // Edit Mass Edit Record
    $('#edit_record').click(function(){
        var deleteids_arr = [];
        
        $("input:checkbox[class=delete_check]:checked").each(function () {
            deleteids_arr.push($(this).val());
        });
        if(deleteids_arr.length > 0){
            var idString = deleteids_arr.join(',');
            var currentUrl = window.location.href;
            var updatedUrl = currentUrl + '&check_id=' + idString;
            window.history.replaceState({}, document.title, updatedUrl);
            window.location.href = updatedUrl;
        }
    });


    // Delete record
    $('#delete_record').click(function(){

        var deleteids_arr = [];
        // Read all checked checkboxes
        $("input:checkbox[class=delete_check]:checked").each(function () {
         deleteids_arr.push($(this).val());
        });

        // Check checkbox checked or not
        if(deleteids_arr.length > 0){

         // Confirm alert
         var confirmdelete = confirm("Do you really want to Delete records?");
         if (confirmdelete == true) {
            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'modaboost_mass_delete',
                    request: 2,
                    deleteids_arr: deleteids_arr
                },
                success: function(response){
                  window.location.reload();
                }
            }); 
         } 
         else{
          alert('Select the Checkbox If You Del')
         }
        }
    });
    // Checkbox checked
    function checkcheckbox(){

       // Total checkboxes
       var length = $('.delete_check').length;

       // Total checked checkboxes
       var totalchecked = 0;
       $('.delete_check').each(function(){
          if($(this).is(':checked')){
             totalchecked+=1;
          }
       });

       // Checked unchecked checkbox
       if(totalchecked == length){
          $("#checkall").prop('checked', true);
       }else{
          $('#checkall').prop('checked', false);
       }
    }

    $(document).on('click', '.slider.round', function(e) {
        //e.preventDefault();
        var checkboxvalue;
        checkboxvalue   = $(this).parent('label.switch').find('input:checkbox').is(":checked");
        id              = $(this).parent('label.switch').find('input:checkbox').data("id");
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'moodaboost_ajax_action',
                checkboxvalue: checkboxvalue,
                id: id,
            },
            beforeSend: function() {
                // Perform any pre-AJAX call actions here, such as showing a loading spinner.
            },
            success: function(response) {
                // Handle the AJAX response here.
                //console.log(response);
                //if(response.success){
                    //alert("Zoho Invoice Proceessed. Please check in your Zoho Invoice Account");
                //}
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors here.
                console.log(error);
            }
        });
    });
}); 