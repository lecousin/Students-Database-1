/* global variable containing data about selected items */
var selected={};

/*
 * Create data list for showing applicants attached to an exam session
 */
function createDataList(campaign_id)
{
   new data_list(
                   "session_applicants_list",
                   "Applicant", campaign_id,
                   [
                           "Personal Information.First Name",
                           "Personal Information.Last Name",
                           "Personal Information.Gender",
                           "Personal Information.Birth Date"
                   ],
                   [],
                   -1,
                   "Personal Information.Last Name", true,
                   function(list) {
                           window.dl = list;
                   }
           );
}

function initResults(){

   $("#session_info_location").hide();
   $("#session_applicants_list").hide();
   

// Update the exam session info and applicants list boxes
   $("tr.clickable_row").click(function(){
      
      $("#session_info_location").show();
      
      // display selected row 
      $(this).addClass("selectedRow");
      $(this).siblings().removeClass("selectedRow");
      
      // get the exam session's data for the selected row
      selected["session_name"]= $(this).children().eq(0).text();
      selected["room_name"]= $(this).children().eq(1).text();      
      selected["exam_center_name"]=$(this).prevAll(".exam_center_row").first().children().eq(0).text();

      //Show a loader gif while waiting for updating
      var loader_img = $("<img>", {id: "loaderResultsImg", src: "/static/application/loading_100.gif"});
      loader_img.css({"display":"block","margin":"0 auto"});
      $("#session_info_location").html(loader_img);
      
      // update exam session information box
      updateExamSessionInfo();
      
      selected["exam_center_id"]=this.getAttribute("exam_center_id");
      selected["session_id"]=this.getAttribute("session_id");
      selected["room_id"]=this.getAttribute("room_id");
      
      // update applicants list
      updateApplicantsList();
      
      $("#session_applicants_list").show();
      
   });
   
   /* Edit Notes button has been clicked */
   $("#edit_notes").click(function(){
     
   /*  Check if an exam session row is selected */ 
     if(selected["session_id"] != null)
      {
         
            /* open a new window pop up for results edition */
            window.top.popup_frame(
                              "/static/selection/exam/results_edit.jpg",
                              "Exam Session Results",
                              "/dynamic/selection/page/exam/results_edit",
                              selected,
                              95, 95,
                              function(frame, pop) {}
                                );
      }
   });

/*
*
* update display of exam session information box
*/
function updateExamSessionInfo() { 

      
   // Get exam center location from exam center name
      service.json("selection","exam/get_exam_center_location",{id:selected["exam_center_id"]},function(host_addr){   
      
      // Get the Postal Address from host address id's  
         service.json("contact","get_address",{"id":host_addr},function(postal_addr){
                var ec_addr_div= new address_text(postal_addr);
                $(ec_addr_div.element).prepend("<span><strong>Location :</strong></span>");
                $(ec_addr_div.element).append("<span><strong> Status :</strong>TODO !</span>");
                $("#session_info_location").html(ec_addr_div.element);
         });
      });
}

function updateApplicantsList() {
   
   /* Check if the data list is already initialized */
   if (!window.dl) {
      setTimeout(function() {
         updateApplicantsList();
      },10);
      return;
   }
   window.dl.resetFilters();
   window.dl.addFilter({category:"Selection",name:"Exam Session",force:true,data:{values:[selected["session_id"]]}});
   window.dl.addFilter({category:"Selection",name:"Exam Center Room",force:true,data:{values:[selected["room_id"]]}});

   window.dl.reloadData();
   }
}