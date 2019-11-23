grecaptcha.ready(function() {
    var action = null;
    var actionObject = {};

    if(document.getElementById('g-recaptcha')) {
        action = document.getElementById('g-recaptcha').getAttribute('data-action');
    }
    
    if(action !== null) actionObject = {action: action};

    grecaptcha.execute('6LfTR8QUAAAAAFmPyAzUrR_26zg-aJjUNo4_dgQq', actionObject).then(function(token) {
        if(document.getElementById('g-recaptcha')) document.getElementById('g-recaptcha').value = token;        
    });
});