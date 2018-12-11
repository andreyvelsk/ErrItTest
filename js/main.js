$(function() {
    var $search = $("#search");
    console.log("is loaded");
    $search.on('input', function (){
        console.log($(this).val());
        $.ajax({
            dataType: 'json',
            url: 'search.php',
            data: "value=" + $(this).val(),
            success: function(jsondata){
              console.log(jsondata);
            },
            error: function() {
                console.log("some error occured");
            }
          });
          
                
    })
});