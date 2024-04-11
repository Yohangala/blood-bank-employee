<?php
$servername = "localhost";
$username = "root";
$password = '';
$dbname = "blood";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];

    switch ($action) {
        case "create":
            $name = $_POST["name"];
            $age = $_POST["age"];
            $gender = $_POST["gender"];
            $address = $_POST["address"];

            $sql = "INSERT INTO Employee (Name, Age, Gender, Address) VALUES ('$name', $age, '$gender', '$address')";
            $result = $conn->query($sql);

            if ($result) {
                $employeeId = $conn->insert_id;
                $employee = array(
                    'id' => $employeeId,
                    'name' => $name,
                    'age' => $age,
                    'gender' => $gender,
                    'address' => $address
                );
                echo json_encode($employee);
            } else {
                echo json_encode(array('error' => "Error creating employee: " . $conn->error));
            }
            break;

        case "edit":
            $employeeId = $_POST["employeeId"];
            $name = $_POST["name"];
            $age = $_POST["age"];
            $gender = $_POST["gender"];
            $address = $_POST["address"];

            $sql = "UPDATE Employee SET Name='$name', Age=$age, Gender='$gender', Address='$address' WHERE Id=$employeeId";
            $result = $conn->query($sql);

            if ($result) {
                echo json_encode(array('success' => "Employee updated successfully!"));
            } else {
                echo json_encode(array('error' => "Error updating employee: " . $conn->error));
            }
            break;

        case "delete":
            $employeeId = $_POST["employeeId"];

            $sql = "DELETE FROM Employee WHERE Id=$employeeId";
            $result = $conn->query($sql);

            if ($result) {
                echo json_encode(array('success' => "Employee deleted successfully!"));
            } else {
                echo json_encode(array('error' => "Error deleting employee: " . $conn->error));
            }
            break;

        default:
            echo json_encode(array('error' => "Invalid action"));
    }

    // End the script execution after sending JSON response
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
    <style>
        /* CSS styles for table */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <form id="employeeForm" method="POST">
        <h2>Add Employee</h2>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="age">Age:</label>
        <input type="number" id="age" name="age" required>

        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required>

        <input type="hidden" name="action" value="create">
        <button type="submit">Add Employee</button>

        <!-- <button><a href="fetch_employees.php" >Donate Now </a></button> -->
    </form>

    <h2>All Employees</h2>
    <table id="employeeTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data rows will be added dynamically here -->
            <?php
            $sql = "SELECT * FROM Employee";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["Id"] . "</td>";
                    echo "<td>" . $row["Name"] . "</td>";
                    echo "<td>" . $row["Age"] . "</td>";
                    echo "<td>" . $row["Gender"] . "</td>";
                    echo "<td>" . $row["Address"] . "</td>";
                    echo "<td>";
                    echo "<button onclick=\"editEmployee(${row['Id']}, '${row['Name']}', ${row['Age']}, '${row['Gender']}', '${row['Address']}')\">Edit</button>";
                    echo "<button onclick=\"deleteEmployee(${row['Id']})\">Delete</button>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No employees found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        function addEmployee() {
            var formData = new FormData(document.getElementById('employeeForm'));

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var employee = JSON.parse(xhr.responseText);
                    addEmployeeToTable(employee);
                }
            };

            xhr.open('POST', 'index.php', true);
            xhr.send(formData);
        }

        function addEmployeeToTable(employee) {
            var tableBody = document.querySelector("#employeeTable tbody");
            var newRow = document.createElement("tr");

            newRow.innerHTML = `
                <td>${employee.id}</td>
                <td>${employee.name}</td>
                <td>${employee.age}</td>
                <td>${employee.gender}</td>
                <td>${employee.address}</td>
                <td>
                    <button onclick="editEmployee(${employee.id}, '${employee.name}', ${employee.age}, '${employee.gender}', '${employee.address}')">Edit</button>
                    <button onclick="deleteEmployee(${employee.id})">Delete</button>
                </td>
            `;

            tableBody.appendChild(newRow);
        }

        function editEmployee(id, name, age, gender, address) {
            var newName = prompt("Enter new name:", name);
            var newAge = prompt("Enter new age:", age);
            var newGender = prompt("Enter new gender (Male/Female):", gender);
            var newAddress = prompt("Enter new address:", address);

            var formData = new FormData();
            formData.append('action', 'edit');
            formData.append('employeeId', id);
            formData.append('name', newName);
            formData.append('age', newAge);
            formData.append('gender', newGender);
            formData.append('address', newAddress);

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    location.reload();
                }
            };

            xhr.open('POST', 'index.php', true);
            xhr.send(formData);
        }

        function deleteEmployee(id) {
            if (confirm("Are you sure you want to delete this employee?")) {
                var formData = new FormData();
                formData.append('action', 'delete');
                formData.append('employeeId', id);

                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        location.reload();
                    }
                };

                xhr.open('POST', 'index.php', true);
                xhr.send(formData);
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
