/*
function p(action, handler, callback){

}
p('signup', this, function {

})
*/
var base_url = '/';
function doLike(type, id, uid){
    var target = $("#likes_count_"+id);
    $.ajax({
        type: "POST",
        cache: false,
        dataType: 'json',
        url: base_url+"api/ajax.php?action=like",
        data: { "type": type, "pid": id, "uid": uid },
        success: function(data) {
            if (type == "user"){
                // Toggle follow/unfollow button
                var btn = $("#follow_button");
                var currentText = btn.text().trim();
                if (currentText == "Unfollow" || currentText == "Following") {
                    btn.html("Follow");
                    btn.removeClass("following").addClass("follow");
                } else {
                    btn.html("Unfollow");
                    btn.removeClass("follow").addClass("following");
                }
                // Update follower count if visible
                if (data && data['follower_count'] !== undefined) {
                    $("#follower_count").html(data['follower_count']);
                }
            } else {
                if(data && data['post_count']){
                    target.html(data['counter']);
                    var thumbsUp = $("#thumbs-up-"+id);
                    var likeUp = $("#like-up-"+id);
                    if (thumbsUp.css("color") == "rgb(45, 136, 255)"){
                        thumbsUp.css("color","rgb(180, 183, 187)");
                        likeUp.css("color","rgb(180, 183, 187)");
                    } else {
                        thumbsUp.css("color","rgb(45, 136, 255)");
                        likeUp.css("color","rgb(45, 136, 255)");
                    }
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Like error:", error);
        }
    });
}
function addPost(pid){
    $.ajax({
        type: "POST",
        contentType: false,
        cache: false,
        processData:false,
        contentType: 'application/x-www-form-urlencoded',
        url: base_url+"api/process.php?action=addpost",
        data: $.param({ "pid": pid}),
        success: function(data) {
            if ((!$.trim(data)) || data == null){   
                //the data is null or blank
            }else{
                $("#create_post_textarea").val('');
                $("#post_section").prepend(data);
            }
        },
        error: function(data) {
            // Some error in ajax call
            alert("some Error");
        }
    });
}

function addComment(pid,commentid){
    $.ajax({
        type: "POST",
        contentType: false,
        cache: false,
        processData:false,
        contentType: 'application/x-www-form-urlencoded',
        url: base_url+"api/process.php?action=addcomment",
        data: $.param({"pid" : pid, "cid": commentid}),
        success: function(data) {
            if ((!$.trim(data)) || data == null){   
                //the data is null or blank
            }else{
                $("#commentText_"+pid).val('');
                $("#comment_post_"+pid).prepend(data);
            }
        },
        error: function(data) {
            // Some error in ajax call
            alert("some Error");
        }
    });
}
$(document).ready(function() {
    $( "#signup_form" ).on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            contentType: false,
            cache: false,
            processData:false,
            url: base_url+"api/ajax.php?action=signup",
            data: new FormData(this), //$("#signup_form").serialize(),
            success: function(data) {
                if(data['error']==true){
                    $('#msg_text').html(data['message']);
                    $('#msg_text').css("display","block");
                    $('#msg_text').css("background","#f8d7da");
                }else{
                    $('#msg_text').html("Your registration has been successfully completed. You have just been sent an email containing membership activation link.");
                    $('#msg_text').css("display","block");
                    $('#msg_text').css("background","#4BB543");
                }
                // Ajax call completed successfully
                //alert("Form Submited Successfully");

            },
            error: function(data) {
                // Some error in ajax call
                alert("some Error");
            }
        });
    });

    $( "#login_button" ).click(function() {
        $.ajax({
            type: "POST",
            url: base_url+"api/ajax.php?action=login",
            data: $("#login_form").serialize(),
            success: function(data) {
                if(data['error']){
                    $('#msg_text').html(data['message']).show();      
                }else{
                    window.location.href = "/home.php"; //to get rid of 302 Found
                    // $('#msg_text').toggleClass('success-text');
                    // $('#msg_text').html(data['message']).show();
                }
                // Ajax call completed successfully
                //alert("Form Submited Successfully");

            },
            error: function(data) {
                // Some error in ajax call
                alert("some Error");
            }
        });
    });

    $( "#forget_button" ).click(function() {
        $.ajax({
            type: "POST",
            url: base_url+"api/ajax.php?action=forget",
            data: $("#forget_form").serialize(),
            success: function(data) {
                if(data['error']){
                    $('#msg_text').html(data['message']).show();
                    $('#msg_text').css("display","block");
                    $('#msg_text').css("background","#f8d7da");    
                }else{
                    $('#msg_text').html(data['message']).show();
                    $('#msg_text').css("display","block");
                    $('#msg_text').css("background","#4BB543");
                }
                // Ajax call completed successfully
                //alert("Form Submited Successfully");

            },
            error: function(data) {
                // Some error in ajax call
                alert("some Error");
            }
        });
    });
    $( "#newpassword_button" ).click(function() {
        $.ajax({
            type: "POST",
            url: base_url+"api/ajax.php?action=newpassword",
            data: $("#newpassword_form").serialize(),
            success: function(data) {
                if(data['error']){
                    $('#msg_text').html(data['message']).show();
                    $('#msg_text').css("display","block");
                    $('#msg_text').css("background","#f8d7da");    
                }else{
                    window.location = "/login.php"
                }
                // Ajax call completed successfully
                //alert("Form Submited Successfully");

            },
            error: function(data) {
                // Some error in ajax call
                alert("some Error");
            }
        });
    });
    $( "#post_form" ).on('submit', function(e) {
        e.preventDefault();
        var form = this;
        $.ajax({
            type: "POST",
            contentType: false,
            cache: false,
            processData:false,
            url: base_url+"api/ajax.php?action=post",
            data: new FormData(this),
            success: function(data) {
                if(data['success']){
                    addPost(data['pid']);
                    // Clear the form after successful post
                    $("#create_post_textarea").val('');
                    $("#image").val('');
                    form.reset();
                }
            },
            error: function(data) {
                // Some error in ajax call
                alert("Error creating post");
            }
        });
    });

   $( "#profile_image_form" ).on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            contentType: false,
            cache: false,
            processData:false,
            url: base_url+"api/ajax.php?action=profileimg",
            data: new FormData(this),
            success: function(data) {
                if(data['updateimg']){
                    $("#myprofile_image1").attr("src",data['img']);
                    $("#myprofile_image2").attr("src",data['img']);
                    $("#myprofile_image3").attr("src",data['img']);
                    addPost(data['pid']);
                }
            },
            error: function(data) {
                // Some error in ajax call
                alert("error");
            }
        });
    });

   $( "#delete_post_form" ).on('submit', function(e) {
        //e.preventDefault();
        $.ajax({
            type: "POST",
            contentType: false,
            cache: false,
            processData:false,
            url: base_url+"api/ajax.php?action=deletepost",
            data: new FormData(this),
            success: function(data) {
                if (data['success']){
                    window.location.href = "/profile.php";
                }
            },
            error: function(){
                window.location.href = "/profile.php";
            }
        });
    });

   $( "#edit_post_form" ).on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            contentType: false,
            cache: false,
            processData:false,
            url: base_url+"api/ajax.php?action=editpost",
            data: new FormData(this),
            success: function(data) {
                if (data['success']){
                    window.location.href = "/profile.php";
                }
            },
            error: function(){
                window.location.href = "/profile.php";
            }
        });
    });
});

function addCommento(pid){
    var form = "add_comment_form_"+pid;
    var formElement = document.getElementById(form);
    $.ajax({
        type: "POST",
        contentType: false,
        cache: false,
        processData:false,
        url: base_url+"api/ajax.php?action=addcomment",
        data: new FormData(formElement),
        success: function(data) {
            if(data['success']){
                addComment(data['postid'],data['commentid']);
                $("#comment_count_"+data['postid']).html(data['comment_counter']+" Comment");
                // Clear the comment form
                $("#commentText_"+pid).val('');
                $("#commentImage").val('');
            }
        },
        error: function(data) {
            // Some error in ajax call
            alert("Error adding comment");
        }
    });
}

function deleteComment(cid){
    $.ajax({
        type: "POST",
        contentType: false,
        cache: false,
        processData:false,
        contentType: 'application/x-www-form-urlencoded',
        url: base_url+"api/ajax.php?action=deletepost",
        data: $.param({ "postid": cid}),
        success: function(data) {
            if (data['success']){
                $("#comment_count_"+data['pid']).html(data['comment_counter']+" Comment");
                $("#comment_"+cid).remove();
            }
        },
        error: function(data) {
            // Some error in ajax call
            alert("some Error "+data);
        }
    });
}

function whoLikes(pid){
    $(".likes_list_wrapper").css("display","flex");
    $(".likes_list_wrapper").attr("state" , "visible");

    $.ajax({
        type: "POST",
        contentType: false,
        cache: false,
        processData:false,
        contentType: 'application/x-www-form-urlencoded',
        url: base_url+"api/process.php?action=getlikes",
        data: $.param({ "pid": pid}),
        success: function(data) {
            if ((!$.trim(data)) || data == null){   
                //the data is null or blank
            }else{
                $(".person_liked").remove();
                $("#likes_list_container").append(data);
            }
        },
        error: function(data) {
            // Some error in ajax call
            alert("some Error");
        }
    });
}
var gerror = null;
$(".confirmPassButton").click(function(){
    var form = "settings_form";
    $.ajax({
        type: "POST",
        contentType: false,
        cache: false,
        processData:false,
        url: base_url+"api/ajax.php?action=settings",
        data: new FormData(document.getElementById(form)),
        success: function(data) {
            if(data['error']==true){
                //there is error
                $("#settings_msg_error").css("background","indianred");
                $("#settings_msg_error").css("display","inline-block");
                $("#settings_msg_error").html(data['message']);
            }else{
                $("#settings_msg_error").css("background","forestgreen");
                $("#settings_msg_error").css("display","inline-block");
                $("#settings_msg_error").html(data['message']);
                $("#fullname_profile").html(data['fullname']);
                $("#email_profile").html(data['email']);
                $("#about_profile").html(data['about']);
            }

        },
        error: function(data) {
            // Some error in ajax call
            gerror = data;
            alert(data);
        }
    });
    $(".confirmPassWrapper").css("display" ,"none");
});

const searchBar = document.getElementById("SearchInputInside");
usersList = document.getElementById("userList_search");
if (searchBar) searchBar.onkeyup = ()=>{
  let searchTerm = searchBar.value;
  if(searchTerm != ""){
      $.ajax({
          type: "POST",
          contentType: false,
          cache: false,
          processData:false,
          contentType: 'application/x-www-form-urlencoded',
          url: base_url+"api/process.php?action=thodzsearch",
          data: $.param({ "searchfor": searchTerm}),
          success: function(data) {
              if ((!$.trim(data)) || data == null){   
                  //the data is null or blank
              }else{
                  usersList.innerHTML = data;
              }
          },
      });
    }
}