/* ======================create Project =========================*/

const createProjectBtn = document.querySelector(".create_project_btn");

if(createProjectBtn) {
  createProjectBtn.addEventListener("click", function () {
    let projectInput = document.getElementById("projectInput");
    let addProjectBtn = document.getElementById("addProjectBtn");

    if (!projectInput && !addProjectBtn) {
      projectInput = document.createElement("input");
      projectInput.id = "projectInput";

      addProjectBtn = document.createElement("button");
      addProjectBtn.id = "addProjectBtn";
      addProjectBtn.appendChild(document.createTextNode("Add Project"));

      const ProjectList = document.querySelector(".project_list");
      ProjectList.appendChild(projectInput);
      ProjectList.appendChild(addProjectBtn);
    }

    addProjectBtn.addEventListener("click", async function () {
      const formData = new FormData();
      formData.append("project_name", projectInput.value);
      formData.append("action", "create_project");

      try {
        const response = await fetch(ajax_object.ajax_url, {
          method: "POST",
          body: formData,
        });

        const data = await response.json();

        if (data.success) {
          console.log("Server Response:", data);
          alert(data.data.message);
          location.reload();
        } else {
          alert(data.data.message);
        }
      } catch (error) {
        console.error("Error:", error);
        alert("An error occurred. Please try again.");
      }
    });
  });
}

/* ======================create Team =========================*/

const createTeamBtn = document.querySelector(".create_team_btn");

createTeamBtn.addEventListener("click", function () {
  let teamNameInput = document.getElementById("team_input");
  let addTeamBtn = document.getElementById("add_team_btn");

  if (!teamNameInput && !addTeamBtn) {

    label = document.createElement("div");
    label.textContent = "Please Input Team Name:";
    teamNameInput = document.createElement("input");
    teamNameInput.id = "team_input";

    addTeamBtn = document.createElement("button");
    addTeamBtn.id = "add_team_btn";
    addTeamBtn.appendChild(document.createTextNode("Add Team"));

    const teamList = document.querySelector(".team_list");
    teamList.appendChild(label);
    teamList.appendChild(teamNameInput);
    teamList.appendChild(addTeamBtn);
  }

  addTeamBtn.addEventListener("click", async function () {
    const formData = new FormData();
    formData.append("team_name", teamNameInput.value);
    formData.append("action", "create_team");

    try {
      const response = await fetch(ajax_object.ajax_url, {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        console.log("Server Response:", data);
        alert(data.data.message);
        location.reload();
  
      } else {
        alert(data.message);
      }
    } catch (error) {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    }
  });
});

const teamMemberForm = document.getElementById("team_member_form");
const inputAddMember = document.getElementById("add_team_member_field");
const addMemberBtn = document.getElementById("add_team_member_btn");
const team_id = document.getElementById("teams");

teamMemberForm.addEventListener("submit", async function (e) {
  e.preventDefault();
  const formData = new FormData();
  formData.append("email", inputAddMember.value);
  formData.append("team_id", team_id.value);
  formData.append("action", "send_invite");

  try {
    const response = await fetch(ajax_object.ajax_url, {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      console.log("Server Response:", data);
      alert("Invite Senrt Successfully!");
      location.reload();
  
    } else {
      console.log(data.data.message);
      alert(data.data.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("An error occurred. Please try again.");
  }
});

// avatar link https://i.pravatar.cc/48

const selectedTeam = document.getElementById("selected_team");

if (selectedTeam) {
  selectedTeam.addEventListener("change", async function () {
    // alert("test");

    if(this.value == "Select From Team"){ 
      alert(this.value);
    }
    const team_id = this.value;

    const formData = new FormData();
    formData.append("team_id", team_id);
    formData.append("action", "get_team_member");

    try {
      const response = await fetch(ajax_object.ajax_url, {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        console.log("Server Response:", data);
        // alert(data.data.message);
        const selectElement = document.getElementById("member");

        // Clear existing options
        selectElement.innerHTML = "";
        const emails = data.data.message;

        // Add new options from the array
        emails.forEach((email) => {
          const option = document.createElement("option");
          option.classList.add("option_email");
          option.value = email;
          option.textContent = email; // Using the same email for the display text
          selectElement.appendChild(option);
        });
      } else {
        console.log(data.data.message);
        // alert(data.data.message);
      }
    } catch (error) {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    }
  });
}

const updateProject = document.getElementById("project_details_form");

if (updateProject) {

updateProject.addEventListener("submit", async function (e) {
  e.preventDefault();

  const project_id = document.getElementById("project").value;
  const projectd_details = document.getElementById("projectd_details").value;
  const due_date = document.getElementById("due_date").value;
  const priority = document.getElementById("priority").value;
  const status = document.getElementById("status").value;


  const formData = new FormData();
  formData.append("project_id", project_id);
  formData.append("projectd_details", projectd_details);
  formData.append("due_date", due_date);
  formData.append("priority", priority);
  formData.append("status", status);


  formData.append("action", "update_project");

  try {
    const response = await fetch(ajax_object.ajax_url, {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      console.log("Server Response:", data);
    } else {
      console.log(data.data.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("An error occurred. Please try again.");
  }
});
}

const addMemberToProject = document.getElementById("add_member_to_project_form");

if(addMemberToProject){


 addMemberToProject.addEventListener("submit", async function (e) {
  e.preventDefault();

  const project_id = document.getElementById("project").value;
  const member = document.getElementById("member").value;

  const formData = new FormData();
  formData.append("project_id", project_id);
  formData.append("member", member);
  formData.append("action", "add_member_to_project");

  try {
    const response = await fetch(ajax_object.ajax_url, {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      console.log("Server Response:", data);
    } else {
      console.log(data.data.message);
    }
  } catch (error) {
    console.error("Error:", error);
    alert("An error occurred. Please try again.");
  }
} );

}