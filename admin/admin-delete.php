<?php

require __DIR__ . "/../config/function.php";

$paraResultId= checkParamId('id');

if(is_numeric($paraResultId)) {
    $adminId = validated($paraResultId);
    
    $admin = getById('admins', $adminId);
    if($admin['status'] == 200) {
        $adminDeleteRes = delete('admins', $adminId);

        if($adminDeleteRes) {
            redirect('admins.php', 'Admin Deleted Successfully');
        } else {
            redirect('admins.php', 'Something went wrong!', 'error');
        }
    } else {
        redirect('admins.php', $admin['message']);
    }
} else {
    redirect('admins.php', 'Something went wrong!', 'error');
}
