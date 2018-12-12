$(function() {
    var $search = $("#search");

    $("#form").submit(function(e) {
        e.preventDefault();
    })
    $search.on('input', function (){
        console.log($(this).val());
        $.ajax({
            dataType: 'json',
            url: 'search.php',
            data: "value=" + $(this).val(),
            success: function(jsondata){
                console.log(jsondata);
                var html = '';
                $.each(jsondata, function(key, value){
                    if(value.type=="offer") {
                        
                        html += '<div class="item">';

                        html += '<div class="item_image">';
                            html += '<img src="' + value.pic + '" alt="' + value.name + '">';
                        html += '</div>'; // close item-image

                        html += '<div class="item_description">';
                            html += '<div class="type">' + Товар + '</div>';
                            html += '<div class="name">' + value.name + '</div>';
                            html += '<div class="price">' + value.price + '</div>';
                        html += '</div>'; // close item-description

                        html += '</div>'; // close item
                    }
                    if(value.type=="category") {
                        
                        html += '<div class="item">';
                        
                        html += '<div class="item_image">';
                            html += '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Gnome-applications-science.svg/267px-Gnome-applications-science.svg.png" alt="' + value.name + '">';
                        html += '</div>'; // close item-image

                        html += '<div class="item_description">';
                            html += '<div class="type">' + Категория + '</div>';
                            html += '<div class="name">' + value.name + '</div>';
                            html += '<div class="count">Товаров в категории: ' + value.count + '</div>';
                            html += '<div class="price">Минимальная цена товара: ' + value.minprice + '</div>';
                        html += '</div>'; // close item-description

                        html += '</div>'; // close item
                    }
                });

                $('.search-output').html(html);
            },
            error: function() {
                console.log("some error occured");
            }
          });
          
                
    })
});