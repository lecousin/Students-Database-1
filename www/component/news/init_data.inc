<?php 
SQLQuery::get_db_system_without_security()->execute("INSERT INTO News (`section`,`domain`,`username`,`html`,`timestamp`,`update_timestamp`) VALUE ('application','Dev','admin','Welcome in PN Students Management Software','".(time()-10)."','".(time()-10)."')");
SQLQuery::get_db_system_without_security()->execute("INSERT INTO News (`section`,`domain`,`username`,`html`,`timestamp`,`update_timestamp`,`reply_to`) VALUE ('application','Dev','admin','When fully implemented, each part of the application will have the possibility to have this kind of updates.','".(time()-9)."','".(time()-9)."',1)");
SQLQuery::get_db_system_without_security()->execute("INSERT INTO News (`section`,`domain`,`username`,`html`,`timestamp`,`update_timestamp`,`reply_to`) VALUE ('application','Dev','admin','Messages are organized into sections and categories: we have a <i>General</i> section, an <i>Education</i> section, a <i>Selection</i> section...<br/>Inside Education, we have categories like <i>Discipline</i>, <i>Health</i>...<br/>Then messages may be assigned to specific items, like <i>Batch XXX</i>, or <i>Class YYY of batch XXX</i>.','".(time()-8)."','".(time()-8)."',1)");
SQLQuery::get_db_system_without_security()->execute("INSERT INTO News (`section`,`domain`,`username`,`html`,`timestamp`,`update_timestamp`,`reply_to`) VALUE ('application','Dev','admin','Users will be also able to post messages, in the different sections and categories, if they have the necessary rights.','".(time()-7)."','".(time()-7)."',1)");
?>