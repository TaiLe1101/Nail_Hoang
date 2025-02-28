<?php
function construct()
{
    load('lib', 'valitation');
    load_model('index');
}
function indexAction()
{
    load_view('index');
}
function addAction()
{
    if (isset($_POST['btn-add'])) {
        global $error;
        //show_array($_POST);
        //Kiểm tra thông tin
        $error = array();
        // if(empty($_POST['category_id'])){
        //     $error['category_id'] = "Id danh mục không được để trống";
        // }else{
        //     if(check_categoey_id($_POST['category_id'])){
        //         $category_id = $_POST['category_id'];
        //     }else{
        //         $error['category_id'] = "Id danh mục không tồn tại trên hệ thống";
        //     }
        // }
        if (empty($_POST['post_title'])) {
            $error['post_title'] = "Tiêu đề không được để trống";
        } else {
            $post_title = $_POST['post_title'];
        }

        //page_content
        if (empty($_POST['post_content'])) {
            $error['post_content'] = "Nội dung bài viết không được để trống";
        } else {
            $post_content = $_POST['post_content'];
        }
        //images
        //Thư mục chứa file load
        $upload_dir = 'public/images/';

        //Đường dẫn của file  sau khi upload.
        $upload_file = $upload_dir . $_FILES['file']['name'];

        //Xử lý khi upload đúng file ảnh
        $type_allow = array('png', 'jpg', 'gif', 'jpeg');
        $type = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($type), $type_allow)) {
            $error['type'] = "Đường dẫn ảnh phải là jpg, png, gif, ipeg";
        } else {
            //Kiểm tra ảnh phải nhỏ hơn 20MB ~ 29.000.000
            $file_size = $_FILES['file']['size'];
            if ($file_size > 29000000) {
                $error['file_size'] = "File ảnh phải nhỏ hơn 20MB";
            }

            //Kiểm tra xem có cùng tên trên hệ thôngs hay không
            if (file_exists($upload_file)) {
                $file_name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
                $new_file_name = $file_name . '- Copy.';
                $new_upload_file = $upload_dir . $new_file_name . $type;

                //Tang chi so khi file do da ton tai
                $k = 1;
                while (file_exists($new_upload_file)) {
                    $new_file_name = $file_name . "- Copy{$k}.";
                    $k++;
                    $new_upload_file = $upload_dir . $new_file_name . $type;
                }
                $upload_file = $new_upload_file;
            }
        }
        if (empty($_FILES['file']['name'])) {
            $error['image_url'] = "Ảnh sản phẩm không được để trống";
        } else {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
                $image_url = $_FILES['file']['name'];
            } else {
                $error['image_url'] = "Upload thất bại";
            }
        }

        //Kiểm tra khi dữ liệu hợp lệ 
        if (empty($error)) {
            $data = array(
                'image_url' =>  $image_url,
            );
            $data = array(
                //'user_id' => $user_id,
                'post_title' => $post_title,
                'post_content' => $post_content,
                //'category_name' => $category_name,
                'image_url' => $image_url,
            );
            insert_post($data);
            redirect("?mod=post&action=list_post");

        }
    }
    load_view('add', $data);
    // show_array($data);

}
function editAction()
{
    //id 
    $id = $_GET['id'];


    if (isset($_POST['btn-edit'])) {
        //Kiểm tra dữ liệu
        if (empty($_POST['post_title'])) {
            $error['post_title'] = "Tiêu đề không được để trống";
        } else {
            $post_title = $_POST['post_title'];
        }

        //page_content
        if (empty($_POST['post_content'])) {
            $error['post_content'] = "Nội dung bài viết không được để trống";
        } else {
            $post_content = $_POST['post_content'];
        }
        //id danh muc
        //Xuất dữ liệu, update qua database
        if (empty($error)) {
            $data = array(
                'post_title' => $post_title,
                'post_content' => $post_content,
            );
            show_array($data);
            update_post($id, $data);
            redirect("?mod=post&action=list_post");
        }
    }
    //post_id
    $get_post_by_id = get_post_by_id($id);

    $data['get_post_by_id'] = $get_post_by_id;
    //show_array($data);
    load_view('edit', $data);
}

function deleteAction()
{
    $id = $_GET['id'];
    echo $id;
    delete_post($id);
    redirect("?mod=post&action=list_post");
}
function list_postAction()
{
    if (isset($_POST['btn-search'])) {
        //phân trang list_pages
        $search = $_POST['search'];
        //phân trang list_pages
        $num_rows = db_num_rows("SELECT * FROM `tbl_post` WHERE `post_title` LIKE '%$search%' OR `post_content` LIKE '%$search%'");
        $num_per_page = 5;
        //Tổng số bản ghi
        $total_row  =  $num_rows;
        //Tổng số trang
        $num_page = ceil($total_row / $num_per_page);

        //Trang
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        //Xuất phát
        $start = ($page - 1) * $num_per_page;

        $list_post = get_post_search($search);
        // $list_page = get_list_pages_by_id();
        $data['total_row'] = $total_row;
        $data['num_rows'] = $num_rows;
        $data['num_page'] = $num_page;
        $data['page'] = $page;
        $data['list_post'] = $list_post;
    } else {
        //phân trang list_pages
        $num_rows = db_num_rows("SELECT * FROM `tbl_post`");
        $num_per_page = 5;
        //Tổng số bản ghi
        $total_row  =  $num_rows;
        //Tổng số trang
        $num_page = ceil($total_row / $num_per_page);

        //Trang
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        //Xuất phát
        $start = ($page - 1) * $num_per_page;

        $list_post = get_post($start, $num_per_page);

        // $list_page = get_list_pages_by_id();
        $data['total_row'] = $total_row;
        $data['num_rows'] = $num_rows;
        $data['num_page'] = $num_page;
        $data['page'] = $page;
        $data['list_post'] = $list_post;
    }
    //show_array($data);
    load_view('list_post', $data);
}

