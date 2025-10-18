/*************  ✨ Windsurf Command 🌟  *************/
<?php include ('includes/header.php'); ?>

<div class="container-fluid px-4">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            <h4 class="mb-0">Edit Admin
                <a href="admins.php" class="btn btn-danger float-end">Go Back</a>
            </h4>
        </div>
        <div class="card-body">
            <?php
            if(isset($_GET['id']) && $_GET['id'] != ''){
                $adminId = $_GET['id'];
                $adminData = getById("admins", $adminId);

                if($adminData){
                    if($adminData['status'] == 200){
                        ?>
                        <form action="code.php" method="POST">
                            <input type="hidden" name="adminId" value="<?= htmlspecialchars($adminData['data']['id'], ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="row">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($adminData['data']['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($adminData['data']['email'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <!-- <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div> -->
                                <div class="mb-3">
                                    <label for="number" class="form-label">Phone</label>
                                    <input type="number" class="form-control" id="phone" name="phone" required value="<?= htmlspecialchars($adminData['data']['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="is_ban">Is Ban</label>
                                    <input type="checkbox" id="is_ban" name="is_ban" value="1" style="width: 20px; height: 20px;" <?= $adminData['data']['is_ban'] == true ? 'checked' : '' ?>>
                                </div>
                                <button type="submit" class="btn btn-primary" name="updateAdmin">Update</button>
                            </div>
                        </form>

                        <?php
                    } else {
                        echo '<h5>'.htmlspecialchars($adminData['message'], ENT_QUOTES, 'UTF-8'). '</h5>';
                    }
                } else {
                    echo "<h4>No such ID found</h4>";
                    return false;
                }
            }
            ?>
        </div>
    </div>
</div>

<?php include ('includes/footer.php'); ?>