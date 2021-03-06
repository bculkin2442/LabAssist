<?php
require_once "logic/database/dbCon.php";
require_once "logic/common/commonFunctions.php";

/*
 * Show the board listing.
 */
function showBoardListing()
{
	$html= "   <div class='flex flexVerticalCenter marginBottom10 '>";
	$html.=         forumNavigation('0');
	$html.= "   </div>";
	$html.= paginatedBoardListing();

	return $html;
}

/*
 * Show a particular board
 */
function showBoard($department)
{
	$formURL=basename($_SERVER['REQUEST_URI']);
	$html="";
	$html.= "<script type='text/javascript' src='scripts/searchbar.js'></script>";
	$html.= "<div class='flex columnLayout2 alignCenterFlex marginBottom10'>";
	$html.= "   <div class='flex flexVerticalCenter'>";
	$html.=         forumNavigation('0');
	$html.= "   </div>";
	$html.= "   <div id='createThread'>";
	$html.= "       <form method='GET' action='{$formURL}'>";
	$html.=             preserveOldGETParams();
	$html.= "           <input type='submit' class='btnSmall' value='New Thread' name='newThread'/>";
	$html.= "       </form>";
	$html.= "   </div>";
	$html.= "</div>";

	//$html.= createSearchBar();
	
	$html.= paginatedThreadListing($department);

	return $html;
}

/*
 * Show a particular thread.
 */
function showThread($quid)
{
	$html = "<script type='text/javascript' src='scripts/searchbar.js'></script>";
	$html.= "   <div class='flex flexVerticalCenter marginBottom10'>";
	$html.=         forumNavigation($quid);
	$html.= "   </div>";
	$html.= paginatedQuestionListing($quid);

	return $html;
}

/*
 * Show the forum navigation.
 */
function forumNavigation($quid)
{
	$html="";


	$html.= "<a href='forum.php'>Home</a> / ";

	if(isset($_GET['viewBoard']) && !empty($_GET['viewBoard']))
	{
		$html.= "<a href='forum.php?viewBoard={$_GET['viewBoard']}'>{$_GET['viewBoard']}</a> / ";

		if(isset($_GET['viewThread']) && !empty($_GET['viewThread']))
		{
			$questionName=getQuestionName($quid);
			$html.= "<a href='forum.php?viewBoard={$_GET['viewBoard']}&viewThread={$_GET['viewThread']}'>$questionName</a>";
		}
	}

	return $html;
}

/*
 * Show a paginated board listing.
 */
function paginatedBoardListing()
{
	$html="";
	$paginationParams=getPaginationParameters();
	$html.=generateBoardTable(getBoardList($paginationParams));
	$html.=printBottomPagination($paginationParams,getPaginationCountBoard());

	return $html;
}

/*
 * Show a paginated thread listing.
 */
function paginatedThreadListing($department)
{
	$html="";
	$paginationParams=array_merge(array($department),getPaginationParameters());
	$html.=generateThreadTable(getThreadList($paginationParams));
	$html.=printBottomPagination($paginationParams,getPaginationCountThread(array($department)));

	return $html;
}

/*
 * Show a paginated listing of a question.
 */
function paginatedQuestionListing($question)
{
	$html="";
	$paginationParams=getPaginationParameters();
	$html.=generateThread(getQuestionList(array_merge(array($question),$paginationParams)));
	$html.=replyToThreadTinyMCE();
	$html.=printBottomPagination($paginationParams,getPaginationCountQuestion(array($question)));

	return $html;
}

/*
 * Create a new thread.
 */
function createNewThread()
{
	$html ="";
	$html.=createThreadTinyMCE();

	return $html;
}

/*
 *
 *
 *  TINY MCE INTEGRATION
 *
 *
 *
 */

/*
 * Create a TinyMCE editor for creating a thread.
 */
function createThreadTinyMCE()
{
	$html="";

	$formURL=basename($_SERVER['REQUEST_URI']);
	$classDropDown=genClass($_GET['viewBoard']);

	$html.=<<<HTML
<script src='/libraries/tinymce/tinymce.min.js'></script>
<script>
	tinymce.init({
		selector: '#body',
		theme: 'modern',
		height: 300,
		width: '100%',
		plugins: [
			'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
			'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
			'save table contextmenu directionality emoticons template paste textcolor'
		],
		content_css: 'css/content.css',
		toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons'
	});
</script>
<div class='responseBlock dropShadow'>
	<form class='responseBlockContainer' method='post' action='{$formURL}'>
		<div class='responseBlockHeader'>
			<span class='lightText'><b>Create Thread</b></span>
		</div><!--responseBlockHeader-->
		<div class='threadBlockTitle'>
			<div class='threadBlockTitleText'>
				<p>Title:</p>
			</div>
			<div class='threadBlockTitleInput'>
				<input type='text' class='inputprimaryLarge' name='threadTitle' placeholder='Thread Title' maxlength=60/>
			</div>
			<div class='threadBlockClassText'>
				<p>Class:</p>
			</div>
			<div class='threadBlockClassInput'>
				{$classDropDown}
			</div>
		</div><!--threadBlockTitle-->
		<div class='responseBlockData'>
			<textarea id="body" name="formattedThread"></textarea>
		</div><!--responseBlockData-->
		<div class='responseBlockSubmit'>
			<input type='submit' class='btn' value='Create'/>
		</div><!--responseBlockData-->
	</form><!--responseBlockContainer-->
</div><!--responseBlock-->
HTML;

	return $html;
}

/*
 * Create a TinyMCE editor for replying to a thread.
 */
function replyToThreadTinyMCE()
{
	$html="";

	$formURL=basename($_SERVER['REQUEST_URI']);

	$html.=<<<HTML
<script src='/libraries/tinymce/tinymce.min.js'></script>
<script>
	tinymce.init({
		selector: '#response',
		theme: 'modern',
		height: 300,
		width: '100%',
		plugins: [
			'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
			'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
			'save table contextmenu directionality emoticons template paste textcolor'
		],
		content_css: 'css/content.css',
		toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons'
	});
</script>
<div class='responseBlock dropShadow'>
	<form class='responseBlockContainer' method='post' action='{$formURL}'>
		<div class='responseBlockHeader'>
			<span class='lightText'><b>Quick Reply</b></span>
		</div><!--responseBlockHeader-->
		<div class='responseBlockData'>
			<textarea id="response" name="formattedReply"></textarea>
		</div><!--responseBlockData-->
		<div class='responseBlockSubmit'>
			<input type='submit' class='btn' value='Reply'/>
		</div><!--responseBlockData-->
	</form><!--responseBlockContainer-->
</div><!--responseBlock-->
HTML;

	return $html;
}


/*
 *
 *
 *
 *   Pagination Functionality
 *
 *
 */



/*
 * Get the pagination parameters to use.
 */
function getPaginationParameters()
{
	/*
	 * Get specified values, or use default ones.
	 */
	if( isset($_SESSION['numPerPage']) && !empty($_SESSION['numPerPage']))
	{
		$limit=$_SESSION["numPerPage"];
	}
	else
	{
		$limit = 100;
	}

	if (isset($_GET["page"])&& !empty($_GET["page"])) 
	{ 
		$page  = $_GET["page"]; 
	} 
	else 
	{ 
		$page=1; 
	}

	$startIndex = ($page-1) * $limit;

	return array($startIndex,$limit);
}

/*
 * Generate the board table
 */
function generateBoardTable($dataset)
{
	$html="";

	$html.=<<<HTML
<div class="tableStyleB dropShadow center" id="table">
	<div class="threadList">
		<div class="threadHeader tableHeaderStyleB">
			<div class='deptCol'>
				<div class='deptColText'>
					<b>Department</b>
				</div>
			</div>
			<div class='unansQuestCol'>
				<b>Un-Answered</b>
			</div>
			<div class='totalQuestCol'>
				<b>Total</b>
			</div>
		</div><!--threadHeader-->
		<div class="threadBody tableRowStyleB">
HTML;

	foreach($dataset as $row)
	{
		$html.=<<<HTML
<div class='threadRow'>
	<div class='deptCol'>
		<form action='{$_SERVER['PHP_SELF']}' method='GET'>
			<div class='deptColText'>
				<button type='submit' name='viewBoard' class='buttonToLink' value='{$row['deptid']}'>{$row['deptname']}</button>
			</div>
		</form>
	</div>
	<div class='unansQuestCol'>
		{$row['unanswered_count']}
	</div>
	<div class='totalQuestCol'>
		{$row['question_count']}
	</div>
</div><!--threadRow-->
HTML;

	}

	$html.="</div><!--threadBody-->";
	$html.="</div><!--threadList-->";
	$html.="</div><!--tableContainer-->";

	return $html;
}

/*
 * Generate the thread table.
 */
function generateThreadTable($dataset)
{
	$html="";

	$html.=<<<HTML
<div class="tableStyleB dropShadow center" id="table">
	<div class="threadList">
		<div class="threadHeader tableHeaderStyleB">
			<div class='statusCol'><b>Status</b></div>
			<div class='subjectCol'><b>Subject</b></div>
			<div class='classCol'><b>Class</b></div>
			<div class='infoCol'>
				<div class='infoColText'>
					<b>Info</b>
				</div>
			</div>
		</div><!--threadHeader-->
		<div class="threadBody tableRowStyleB">
HTML;

	foreach($dataset as $row)
	{

		$time= date('F d, h:i a',strtotime($row['added']));

		$html.="<div class='threadRow'>";
		$html.="    <div class='statusCol'>";

		if($row['status']=='answered')
		{
			$html.="<img src='styles/img/icons/answered-question.svg' alt='Answered'>";
		}
		else
		{
			$html.="<img src='styles/img/icons/unanswered-question.svg' alt='Un-Answered'>";
		}

		$html.="    </div>";
		$html.="    <div class='subjectCol'>";
		$html.="        <form action='{$_SERVER['PHP_SELF']}' method='GET'>";
		$html.=             preserveOldGETParams();
		$html.=             "<button type='submit' name='viewThread' class='buttonToLink' value='{$row['quid']}'>{$row['title']}</button>";
		$html.="        </form>";
		$html.="    </div>";
		$html.="    <div class='classCol'>";
		$html.="{$row['classname']}";
		$html.="    </div>";
		$html.="    <div class='infoCol'>";
		$html.="        <div class='infoColText'>";
		$html.="{$row['author']}<br>";
		$html.="<div class='tableDateStyleB'>$time</div>";
		$html.="        </div>";
		$html.="    </div>";
		$html.="    </div><!--threadRow-->";

	}
	$html.="</div><!--threadBody-->";
	$html.="</div><!--threadList-->";
	$html.="</div><!--tableContainer-->";


	return $html;
}



/*
 *
 *   
 *  Generate the posts in the thread.
 *
 *
 */

/*
 * Generate the thread
 */
function generateThread($dataset)
{
	$html="";
	$html.="\n<div class='postList'>\n";

	foreach($dataset as $row)
	{
		$html.="<div class='postBlock dropShadow'>\n";
		$html.="    <div class='postBlockUserInfoContainer'>\n";
		$html.="        <div class='postBlockUserImgContainer'>\n";
		$html.=             getPostUserImg($row['author']);
		$html.="        </div><!--postBlockUserImgContainer-->\n";
		$html.="        <div class='postBlockUserInfo'>\n";
		$html.=             getPostUserInfo($row['author']);
		$html.="        </div><!--postBlockUserInfo-->\n";
		$html.="    </div><!--postBlockUserInfo-->\n";
		$html.="    <div class='postBlockContainer'>\n";
		$html.="        <div class='postBlockHeader'>\n";
		$html.=             getPostHeader($row['author'],$row['added'],$row['is_question'],$row['status']);
		$html.="        </div><!--postBlockHeader-->\n";
		$html.="        <div class='postBlockDataContainer'>\n";
		$html.="            <div class='postBlockData'>\n";
		$html.=                 $row['body'];
		$html.="            </div><!--postBlockData-->\n";
		$html.="        </div><!--postBlockDataContainer-->\n";
		$html.="    </div><!--postBlockContainer-->\n";
		$html.="</div><!--postBlock-->\n";

	}
	$html.="</div><!--postList-->\n";

	return $html;
}

/*
 * Get the avatar for a user.
 */
function getPostUserImg($idno)
{
	$html = "";

	$query =<<<SQL
SELECT ENCODE(ua.avatar, 'base64') AS avatar FROM user_avatars ua WHERE ua.idno = ?
SQL;

	$result = databaseQuery($query, array($idno));

	if(is_array($result) && count($result)!=0)
	{
		$html = "           <div class='postBlockUserImg'>\n";
		$html.= "               <img src=\"data:image/png;base64," . $result[0]["avatar"] . "\"/>\n";
		$html.= "           </div>\n";
	}
	else
	{
		$html = "           <div class='postBlockUserImg'> \n";
		$html.= "               <img src='styles/img/icons/user.svg' class='defaultFill'/>\n";
		$html.= "           </div>\n";
	}

	return $html;
}

/*
 * Get the user info for a post
 */
function getPostUserInfo($idno)
{
	$html = "";

	$query =<<<SQL
SELECT users.realname, users.email, users.role FROM users WHERE users.idno = ?
SQL;
	$result = databaseQuery($query, array($idno));

	if(is_array($result) && !empty($result))
	{
		$html = "           <div class=\"postBlockUserInfo\">\n";
		$html.= "               <p><em><b>{$result[0]['role']}</b></em></p>\n";
		$html.= "               <b>{$result[0]['realname']}</b>\n";
		$html.= "               <em>{$result[0]['email']}</em>\n";
		$html.= "           </div>\n";

	}

	return $html;
}

/*
 * Get the header for a post.
 */
function getPostHeader($author,$date,$is_question,$status)
{
	$formURL=basename($_SERVER['REQUEST_URI']);

	$time = date('M d, h:i a',strtotime($date));

	$html =<<<HTML
<div class='postBlockHeaderWrapper'>
	<div class='postDate'>
		<em>$time</em>
	</div>
HTML;

	if($is_question===true)
	{
		$pred = isUserRoleGreaterThanOrEqualTo($_SESSION['useridno'], 'tutor');
		
		if($_SESSION['useridno']===$author || $pred===1)
		{
			$html.= "       <div class='isSolved'>\n";
			$html.= "           <form name='header' method='POST' action='{$formURL}'>\n";

			if($status=='answered')
			{
				$html.= "           <input id='solvedCheck' onChange='this.form.submit()' name='solvedCheck' type='checkbox' checked>\n";
				$html.= "           <input type='hidden' name='solvedCheck' value='-100' >\n";
				$html.= "           <label for='solvedCheck'><em><b>Solved ?</b></em></label>\n";
			}
			else
			{
				$html.= "           <input id='solvedCheck' onChange='this.form.submit()' name='solvedCheck' type='checkbox'>\n";
				$html.= "           <input type='hidden' name='solvedCheck' value='100' >\n";
				$html.= "           <label for='solvedCheck'><em><b>Solved ?</b></em></label>\n";
			}

			$html.= "           </form>\n";
			$html.= "       </div>\n";
		}
	}
	$html.= "       </div>\n";
	return $html;
}




/*
 *
 *   
 *  End Thread generation.
 *
 *
 */



/*
 * Preserve the get parameters.
 */
function preserveOldGETParams()
{
	$html="";

	$keys = array('viewBoard', 'viewThread', 'viewPost', 'page');

	foreach($keys as $name) 
	{
		if(!isset($_GET[$name])) 
		{
			continue;
		}

		$value = htmlspecialchars($_GET[$name]);
		$name = htmlspecialchars($name);
		$html.= '<input type="hidden" name="'. $name .'" value="'. $value .'">';
	}

	return $html;
}

/*
 * Print out the pagination stuff for the bottom.
 */
function printBottomPagination($paginationValues,$count)
{    
	$total_records = $count[0]['count'];

	if($paginationValues[1]===0)
	{
		$total_pages = 1;
	}
	else
	{
		$total_pages = ceil($total_records / $paginationValues[1]);
	}

	$baseurl=strtok($_SERVER["REQUEST_URI"],'?') . '?';

	foreach($_GET as $index =>$get)
	{
		if($index!='page') {
			$baseurl.=$index.'='.$get.'&';
		}
	}

	$pagLink = "<div class='pagination centerFlex'>";
	$pagLink .= "<ul><li>Page: </li>";  

	for ($i=1; $i<=$total_pages; $i++) 
	{  
		if(empty($_SERVER['QUERY_STRING'])) {
			$pagLink .= "<li><a href='$baseurl?page=".$i."'>".$i."</a></li>";
		} else {
			$pagLink .= "<li><a href='$baseurl"."page=".$i."'>".$i."</a></li>";
		}
	}

	$pagLink.="</ul></div><br><br><br><br>";

	return $pagLink;
}

/*
 * Generate class list.
 */
function genClass($dept)
{
	//Declare array
	$result=array();
	$class="";

	$html="";

	var_dump($dept);
	
	$sql=<<<SQL
SELECT DISTINCT classes.name, sections.cid FROM terms
	JOIN sections ON sections.term = terms.code 
	LEFT JOIN classes ON sections.cid = classes.cid
	WHERE terms.activeterm = TRUE AND dept = ?
SQL;
	$result=databaseQuery($sql, array($dept));
	

	if(!is_array($result) || empty($result))
	{
		return "Could not find any classes. Please contact administrator.";
	}
	else
	{
		$result = filterClasses($result);
	}

	if(isset($_POST['classSelect']) && !empty($_POST['classSelect']))
	{       
		$class=$_POST['classSelect'];
	}

	/*
	 * Generate the html code for the class selection box
	 */
	$html.= "<select name=\"classSelect\" class=\"inputSelect\">";

	var_dump($result);
	
	foreach($result as $row)
	{
		if ($class === $row["cid"])
		{
			$html.= "<option value=\"{$row["cid"]}\" selected>{$row["name"]}</option>";
		}
		else
		{
			$html.= "<option value=\"{$row['cid']}\">{$row['name']}</option>";
		}
	}
	$html.= "</select>";


	/*
	 * Send the "string" of html code back to the calling function
	 */
	return $html;
}

/*
 * Filter out non-fitting classes.
 */
function filterClasses($result)
{
	/*
	 * Filter out anything not accessable by students
	 *
	 * This is any class with the word 'tutor' in its name
	 */
	$curRole = getUserLevelAccessIdno($_SESSION['useridno']);

	if($curRole === 'student')
	{
		$newArray = array_filter($result, function($key, $val) {
			/*
			 * This does a case-insensitve search for the word tutor 
			 * in the name.
			 */
			return !preg_grep("/(?i)tutor/", $val['name']);
		});

		/*
		 * Reindex the array.
		 */
		 
		return array_values($newArray);
	}
	else
	{
        return $result;
    }
}

/*
 *
 *
 *
 *   Database Queries
 *
 *
 */

/*
 * Update the status of a question
 *
 * :UncalledFunction
 */
function updateQuestionStatus($status, $quid)
{
	$result = databaseQuery("UPDATE questions SET status=? WHERE quid=?", array($status, $quid));

	if(!is_array($result) || empty($result))
	{
		return -1;
	}
	else
	{
		return 1;
	}

}

/*
 * Get the name of a question.
 */
function getQuestionName($quid)
{
	$result = databaseQuery("SELECT questions.title FROM questions WHERE questions.quid=?", array($quid));

	if(!is_array($result) || empty($result))
	{
		return "Question not found";
	}
	else
	{
		return $result[0]['title'];
	}
}

/*
 * Create a response.
 */
function createResponse($quid, $author, $reply, $is_question)
{
	$query = <<<SQL
INSERT INTO posts(question, author, body, is_question, added) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
SQL;
	$result=databaseQuery($query, array( $quid, $author, $reply, $is_question));

	if(is_array($result))
	{
		$res = safeDBQuery("SELECT title, asker FROM questions WHERE quid = ?", array($quid));

		$parms = array(
			'recipient' => getUserRealName($res[0]['asker']),
			'nquestions' => 1,
			'questions' => $res[0]['title']
		);

		return postNotification($res[0]['asker'], 'PENDING_QUESTION', $parms);
	}
	else
	{
		return -1;
	}
}

/*
 * Create a thread.
 */
function createThread($questionArr, $postArr)
{
	$query =<<<'SQL'
INSERT INTO questions(subject, term, title, asker, status, added)
	VALUES(?, (SELECT code FROM terms WHERE terms.activeterm = true), ?, ?, 'awaiting_response', CURRENT_TIMESTAMP) 
	RETURNING quid
SQL;

	$result = databaseQuery($query, $questionArr);


	if(is_array($result))
	{
		/*
		 * Get the question ID, and stick it on the array.
		 */
		$temp = $result[0]['quid'];
		array_unshift($postArr,$temp);

		return createResponse($postArr[0], $postArr[1], $postArr[2], true);
	}
	else
	{
		return -1;
	}
}

/*
 * Get the list of threads.
 */
function getThreadList($array)
{
	$query = <<<'SQL'
WITH filt_classes AS (
	SELECT * FROM classes WHERE classes.dept = ?
)
SELECT questions.quid as quid, filt_classes.name AS classname, questions.title, users.realname AS author,
	questions.status, posts.added
	FROM questions
	JOIN filt_classes  ON questions.subject = filt_classes.cid
	JOIN users         ON questions.asker   = users.idno
	JOIN posts         ON questions.quid    = posts.question
	WHERE posts.added = (SELECT MAX(posts.added) FROM posts WHERE posts.question = questions.quid)
		AND questions.term = (SELECT code FROM terms WHERE terms.activeterm = true)
	ORDER BY posts.added DESC
	OFFSET ? LIMIT ?
SQL;

	return databaseQuery($query, $array);
}

/*
 * Get the list of boards.
 */
function getBoardList($array)
{
	return databaseQuery("select * from forum_overview offset ? limit ?",$array);
}

/*
 * Get the list of posts for a question.
 */
function getQuestionList($array)
{
	$query =<<<SQL
SELECT posts.postid, posts.question, posts.author, posts.body, posts.is_question, posts.added, questions.status
	FROM posts
	JOIN questions ON posts.question = questions.quid
	WHERE posts.question=?
	ORDER BY added
	OFFSET ? LIMIT ?
SQL;

	return databaseQuery($query, $array);
}

/*
 * Get the pagination count for threads.
 */
function getPaginationCountThread($array)
{
	$query = <<<SQL
WITH filt_classes AS (
	SELECT * FROM classes WHERE classes.dept = ?
)
SELECT COUNT(questions.title) AS count
	FROM questions
	JOIN sections ON questions.subject = sections.secid
	JOIN terms ON sections.term = terms.code
	JOIN filt_classes ON sections.cid = filt_classes.cid
	WHERE terms.code = (SELECT code FROM terms WHERE activeterm = true)
SQL;

	return databaseQuery($query, $array);
}

/*
 * Get the pagination count for boards.
 */
function getPaginationCountBoard()
{
	return databaseExecute("select count(deptid) as count from departments");
}

/*
 * Get the pagination counts for a question.
 */
function getPaginationCountQuestion($array)
{
	return databaseQuery("select count(body) as count from posts where question=?",$array);
}

/*
 * Get the first name of the currently logged in user.
 *
 * The username must be present in the session.
 */
function getUserRealName($idno)
{
	$sql = "SELECT users.realname FROM users WHERE users.idno=?";

	$result = databaseQuery($sql, array($idno));

	if(is_array($result) && !empty($result)) {
		return trim($result[0]['realname']);
	} else {
		return "To Whom It May Concern";
	}
}
?>
