import { api_get } from './Components/battle_api';

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

(function() {
    var adminElement = document.getElementById('navbarDropdownAdmin');
    function fetchExchangeCount() {
        api_get('/admin/messages/new/count').then(function(response) {
            if(response.data.count > 0) {
                var newSpan = document.createElement("span");
                var newContent = document.createTextNode(response.data.count);
                newSpan.appendChild(newContent);
                newSpan.className = "badge badge-light mx-1";
                adminElement.appendChild(newSpan);
            }
        }).catch(function(error) {});
    }

    if(adminElement !== null) {
        fetchExchangeCount();
    }
})();