// any CSS you require will output into a single css file (app.css in this case)
import '../styles/app.css';
import './../bootstrap';
import { api_get } from './Components/battle_api';

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

(function() {
    var exchangeElement = document.getElementById('js-exchange');

    function fetchExchangeCount() {
        api_get('/exchange/count').then(function(response) {
            if (response.data.count > 0) {
                var newSpan = document.createElement("span");
                var newContent = document.createTextNode(response.data.count);
                newSpan.appendChild(newContent);
                newSpan.className = "badge badge-light mx-1";
                exchangeElement.appendChild(newSpan);
            }
        }).catch(function(error) {});
    }

    if (exchangeElement !== null) {
        fetchExchangeCount();
    }
})();