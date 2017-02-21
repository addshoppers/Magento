init();

var addshop_social = document.getElementById('addshopLoginButtons').innerHTML; 
var addshop_social_ca = document.getElementById('addshopLoginButtonsCa').innerHTML; 

if (document.URL.search('/account/login') > 0) {
    jQuery('.login-form-left').append(addshop_social);
    AddShoppersWidget.onload();
} else if (document.URL.search('account/create') > 0) {
    jQuery('#form-validate').prepend(addshop_social_ca);
    AddShoppersWidget.onload();
}


function init() {
  AddShoppersWidget.API.Event.bind("sign_in", createAccount);		
};

//Create Account 
function createAccount(params) {        
	var siteid = 1;
	var storeid = 1;
	var groupid = 1;
	var apiurl = "https://i.addshoppers.com/merchants/johnnywas/customerjw.php";
  	if (params.source == "social_login") {               
        var data = AddShoppersWidget.API.User.signed_data(); 

        //if on create account page use different api call
        if(document.URL.search("account/create") > 0  ){ 
            var apiurl = "https://WWW.EXAMPLE.COM/soapcreate.php";
        }

        document.body.style.cursor = "progress";
        console.log(apiurl + "?siteid=" + siteid + "&storeid=" + storeid + "&groupid=" + groupid + "&data=" + JSON.stringify(data));
        jQuery.ajax({
            type: "GET",
            url: apiurl + "?siteid=" + siteid + "&storeid=" + storeid + "&groupid=" + groupid + "&data=" + JSON.stringify(data),
            success: function (data) {
                console.log(data);
                if(data == "false"){
                    console.log('failboat');
                    document.body.style.cursor = "default";
                } else {
                    asVars = data;
                    asRay = asVars.split("|");

                    //check length of my accnt and fill in values
                    if (jQuery("#email").length > 0 && asRay[0] == "true") {
                        document.getElementById('email').value = asRay[1];
                        document.getElementById('pass').value = asRay[2];
                        jQuery("#login-form").submit();
                    }


                    if(document.URL.search("account/create") > 0  && asRay[0] == "true" ){ 
                        document.getElementById('firstname').value = asRay[3];
                        document.getElementById('lastname').value = asRay[4];
                        document.getElementById('email_address').value = asRay[1];
                        document.getElementById('password').value = asRay[2];
                        document.getElementById('confirmation').value = asRay[2];
                        jQuery("#form-validate").submit();
                    }

                    document.body.style.cursor = "default";

                }
            },
                        error: function (data) {
                            console.log(data);
                        }
        });

    } else {
        alert('Sorry, could not log you in because your security settings under your ' + params.network + ' account. Please allow access to your email or try another network.');
    }
}