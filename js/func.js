var SERVICE_URL = "http://46.46.87.154:88/test-task/service/";
//var SERVICE_URL = "http://localhost/test/service/";

function loadProfile(){
        var request = {'action':'getUserByToken', 'params':{'accessToken':Cookies.get('accessToken')}};
        $.ajax({
            type: 'POST',
            url: SERVICE_URL+'api.php',
            data: JSON.stringify(request),
            contentType: "application/json; charset=utf-8",
            success: function(data){
                var obj = JSON.parse(data);
                if(obj['error'] == ''){
                    var id = obj['data'].id.$id;
                    var name = obj['data'].name;
                    var lastname = obj['data'].lastname;
                    var email = obj['data'].email;
                    var address = obj['data'].address;
                    var photo = obj['data'].photo;
                    var length = obj['data'].timestamp.length;
                    var timestamp = obj['data'].timestamp[length-2];
                    var visit = new Date(timestamp * 1000);
                    if(timestamp == null){
                        $("#lastvisit").hide();
                    }
                    var isAdmin = obj['data'].isAdmin;
                    $("#name").val(name);
                    $("#lastname").val(lastname);
                    if(isAdmin == false){
                        $("#email").remove();
                        $("#email-group").append("<p id='email' style='margin: 5px 0px 24px;'></p>");
                        $("#email").text(email);
                    }
                    else
                        $("#email").val(email);
                        $("#address").val(address);
                        $("#lastvisit").text(" "+visit.toLocaleString());
                        $( "#photo" ).attr( "src", SERVICE_URL+photo);
                        $("#submit").attr("data-button", id);
                }
                else{
                    window.location.replace("./login.html");
                }
            }
        });
    }

function isAdmin(callback){
    var request = {'action':'checkRole', 'params':{'accessToken':Cookies.get('accessToken')}};

        $.ajax({
            type: 'POST',
            url: SERVICE_URL+'api.php',
            data: JSON.stringify(request),
            contentType: "application/json; charset=utf-8",
            success: function(data){
                var obj = JSON.parse(data);               
                if(obj['error'] == ''){
                    if(obj['data'].isAdmin)                     
                      callback(); 
                }
            }
        });
}

function loadUsers(){
        $("#tbody").html("");
        var request = {'action':'getUsers', 'params':{'accessToken':Cookies.get('accessToken')}};
        $.ajax({
            type: 'POST',
            url: SERVICE_URL+'api.php',
            data: JSON.stringify(request),
            contentType: "application/json; charset=utf-8",
            success: function(data){
                var obj = JSON.parse(data);
                if(obj['error'] == ''){
                    var arr = obj['data'];
                    $.each(arr, function(index, value) {
                        var length = value.timestamp.length;
                        var visit = new Date(value.timestamp[length-1] * 1000);
                        visit = visit.toLocaleString();
                        if(value.timestamp[length-1] == null)
                            visit = "new user";
                        var row = '<tr><td><img alt="pic" class="img-circle" height="50px" width="50px" src="'+SERVICE_URL+value.photo+'"></img></td><td>'+value.name+'</td><td>'+value.lastname+
                        '</td><td>'+value.email+'</td><td>'+value.address+'</td><td id="visit">'+visit+'</td><td class="text-center"><button id="usrEdit" class="btn btn-primary btn-sm" data-button="'+value._id.$id+'" data-toggle="modal" data-target="#usrModal">'+
                        '<span class="glyphicon glyphicon-pencil" style="margin-right: 3px;"></span>Edit</button></td></tr>';
                        $("#tbody").append(row);
                    });
                }
                else{
                  //  window.location.replace("./login.html");
                }
            }
        });
    }

function loadUserModal(data){
    $("#usrerrors ul").empty();
    $("#usrname").val(data['name']);
    $("#usrlastname").val(data['lastname']);
    $("#usremail").val(data['email']);
    $("#usraddress").val(data['address']);
}
