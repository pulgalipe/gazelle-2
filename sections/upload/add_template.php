<?
 
//if ( !check_perms('use_templates') ) error(403);
  


// trim whitespace before setting/evaluating these fields
$Name = db_string(trim($_POST['name']));
$Title =  db_string(trim($_POST['title']));
$Image =  db_string(trim($_POST['image']));
$Body =  db_string(trim($_POST['body']));
$Category = (int)$_POST['category'];
$TagList = db_string(trim($_POST['tags']));
$Public = $_POST['ispublic']==1?1:0;
$UserID = (int)$LoggedUser['ID'];
$TemplateID = (int)$_POST['templateID'];

//TODO: add max number of templates?

if ($Title=='' && $Image=='' && $Body=='' && $TagList=='' ) {
     
    $Result = array(0, "Cannot save a template with no content!");
    
} else {
    
    if(is_number($TemplateID)&& $TemplateID>0) {
        $DB->query("SELECT Name, Public FROM upload_templates WHERE ID='$TemplateID'");
        if($DB->record_count()==0) {
            $Result = array(0, "Could not find template #$TemplateID to overwrite!");
        } else {
            list($Name, $Public) = $DB->next_record();
            $DB->query("UPDATE upload_templates SET UserID='$UserID', 
                                                 TimeAdded='".sqltime()."', 
                                                      Name='$Name',  
                                                     Title='$Title', 
                                                     Image='$Image',  
                                                      Body='$Body', 
                                                CategoryID='$Category', 
                                                   Taglist='$TagList' 
                                   WHERE ID='$TemplateID'"); 
                                                   // Public='$Public',
            if ($Public) $Cache->delete_value('templates_public');
            else $Cache->delete_value('templates_ids_' . $LoggedUser['ID']);
            
            $Cache->delete_value('template_' . $TemplateID);
            $Result = array(1, "Saved '$Name' template");
        }
        
    } elseif ($Name=='') { 
    
        $Result = array(0, "Error: No name set");
    
    } else  {
        
        $DB->query("INSERT INTO upload_templates 
                              (UserID, TimeAdded, Name, Public, Title, Image, Body, CategoryID, Taglist) VALUES 
            ('$UserID', '".sqltime()."', '$Name', '$Public', '$Title', '$Image', '$Body', '$Category', '$TagList')  ");

        $TemplateID = $DB->inserted_id();

        if ($Public) $Cache->delete_value('templates_public');
        else $Cache->delete_value('templates_ids_' . $LoggedUser['ID']);
        
        $Result = array(1, "Added '$Name' template");
        
    }
    
}
 

$Result[] = get_templatelist_html($LoggedUser['ID'], $TemplateID);

echo json_encode($Result);
?>
