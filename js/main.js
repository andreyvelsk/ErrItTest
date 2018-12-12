$(function() {
    var $search = $("#search");

    $("#form").submit(function(e) {
        e.preventDefault();
    })

    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
          clearTimeout (timer);
          timer = setTimeout(callback, ms);
        };
      })();

      $search.keyup(function() {
        val = ($(this).val());
        delay(function(){
            getAjaxData(val);
        }, 500 );
    });
    

    function getAjaxData (val) {
        console.log(val);
        $.ajax({
            dataType: 'json',
            url: 'search.php',
            data: "value=" + val,
            success: function(jsondata){
                console.log(jsondata);
                var html = '';
                $.each(jsondata, function(key, value){
                    html_t = createHtml(value);
                    html += html_t;
                });
                $('.search-output').html(html);
            },
            error: function() {
                cosnole.log("some error occured");
            }
        });
    }

    function createHtml (value) {
        var html = '', type = 'Тип';

        // рандомная картинка, если не указана в xml
        if(!value.pic) value.pic = "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Gnome-applications-science.svg/267px-Gnome-applications-science.svg.png";

        if(value.type=="offer") {
            type = 'Товар';
        }
        if(value.type=="category") {
            type = 'Категория';
        }

        html += '<div class="item">';

        html += '<div class="item_image">';
            html += '<img src="' + value.pic + '" alt="' + value.name + '">';
        html += '</div>'; // close item-image

        html += '<div class="item_description">';
            html += '<div class="type">'+ type +'</div>';
            html += '<div class="name">Наименование: ' + value.name + '</div>';
            if(value.price)
            html += '<div class="price">Цена: ' + value.price + '</div>';
            if(value.count)
            html += '<div class="count">Товаров в категории: ' + value.count + '</div>';
            if(value.minprice)
            html += '<div class="price">Минимальная цена товара: ' + value.minprice + '</div>';
            if(value.available == 'true')
            html += '<div class="price">В наличии</div>';
            if(value.available == 'false')
            html += '<div class="price">Нет в наличии</div>';

        html += '</div>'; // close item-description

        html += '</div>'; // close item


        return html;
    }
});