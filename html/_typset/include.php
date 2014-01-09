<?

$root = __DIR__;

// Connect to database
include "$root/database.php";
$db_info = (object) array(
	"host" => "localhost",
	"database" => "typset",
	"user" => "root",
	"password" => "root"
);
$db = new DB($db_info);
		
class Typset {

	// Global Variables
	protected $template_location = "_typset/templates";
	public $page;

	// Initial Setup
	public function __construct() {
		
		// Load page with variables
		$this->page = str_replace(".php", "", basename($_SERVER['PHP_SELF']));
		
	}
	
	// Error Reporting
	public function error($e) {
		echo $e;
	}

/* Common Functions
--------------------------------- */

/* Merge Options */

	private function options_merge($defaults, $options) {
		if (!isset($options)):
			$options = $defaults;
		else:
			foreach ($defaults as $key => $value):
				if (!isset($options[$key])):
					$options[$key] = $value;
				endif;
			endforeach;
		endif;
		return (object) $options;
	}

/* Respond */

	public function respond($message) {
		$message = json_encode($message);
		header("Content-type: application/json");
		echo $message;
		exit;
	}
	
/* HTML Data Tags */

	public function datatags($data) {
		if (isset($data->options)):
			// Static content
			$data_string = 'data-type="'.$data->options->type.'"';
			if (isset($data->options->tag)):
				$data_string .= ' data-tag="'.$data->options->tag.'"';
			endif;
			if (isset($data->content->id)):
				$data_string .= ' data-id="'.$data->content->id.'"';
			endif;
		else:
			// Sequencial content
			$data_string = 'data-id="'.$data->id.'"';
		endif;
		return $data_string;
	}
	
/* Print Content */

	private function render_content($content, $options) {
	
		// Combine for optional formatting
		$data = (object) array(
			"options" => $options,
			"content" => $content
		);	
		
		if ($options->format === "html"):
			$template_file = "$this->template_location/$options->type.php";
			if (file_exists($template_file)):
				include $template_file;
			else:
				$this->error("Missing html template: $template_file");
			endif;
		elseif ($options->format === "json"):
			$data = json_encode($data);
			echo $data;
		else:
			print_r($data);
		endif;
		
	}
	
/* Truncate */

	private function truncate($content, $truncate) {
		if (strlen($content) > $truncate and $truncate != 0):
			$content = substr($content, 0, $truncate);
			$content = substr($content, 0, strrpos($content, ' '));
			$content .= "&hellip;";
		endif;
		return $content;
	}

/* Blurb
--------------------------------- */

	public function blurb($options=null) {

		global $db;

		// Define defaults
		$defaults = array(
			"type" => "blurb",
			"id" => "",
			"tag" => "",
			"format" => "html"
		);

		// Process options
		$options = $this->options_merge($defaults, $options);
		
		// Get content from database
		$query = "SELECT title,text,id FROM $options->type WHERE tag=:tag LIMIT 1";
		$query_data = array("tag" => $options->tag);
		$statement = $db->run($query, $query_data);
		$results = $statement->rowCount();
		$response = $statement->fetch();
		
		if ($results > 0):
								
			// Build response
			$content = (object) array(
				"title" => $response->title,
				"text" => $response->text,
				"id" => $response->id
			);
		
		else:
		
			// Dummy content
			$content = (object) array(
				"title" => "Nothing Here Yet",
				"text" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
			);
			
		endif;
	
		// Render content
		$this->render_content($content, $options);
								
	}
	
/* HTML
--------------------------------- */

	public function html($options=null) {

		global $db;

		// Define defaults
		$defaults = array(
			"type" => "html",
			"id" => "",
			"tag" => "",
			"format" => "html"
		);

		// Process options
		$options = $this->options_merge($defaults, $options);
		
		// Get content from database
		$query = "SELECT text,id FROM $options->type WHERE tag=:tag LIMIT 1";
		$query_data = array("tag" => $options->tag);
		$statement = $db->run($query, $query_data);
		$results = $statement->rowCount();
		$response = $statement->fetch();
		
		if ($results > 0):
								
			// Build response
			$content = (object) array(
				"text" => $response->text,
				"id" => $response->id
			);
		
		else:
		
			// Dummy content
			$content = (object) array(
				"text" => "HTML code goes here"
			);
			
		endif;
	
		// Render content
		$this->render_content($content, $options);
								
	}	
	
/* Blog
--------------------------------- */	
	
	public function blog($options=null) {
	
		global $db;
		
		// Define defaults
		$defaults = array(
			"type" => "blog",
			"title" => "Latest News",
			"id" => "",
			"tag" => "",
			"truncate" => 0,
			"format" => "html",
			"items" => 10,
			"mode" => "leads", // leads, full
			"sort" => "date",
			"order" => "DESC"
		);
		
		// Process options
		$options = $this->options_merge($defaults, $options);
				
		// Paging
		$paging_name = (!empty($options->id) ? $options->id."_page" : "page");
		if (!isset($$paging_name)) $$paging_name = 1;
		$options->offset = $$paging_name * $options->items - $options->items;
		$options->paging_name = $paging_name;
	
		// Get content from database
		$query = "SELECT title,date,text,id FROM $options->type WHERE tag=:tag ORDER BY $options->sort $options->order LIMIT $options->offset, $options->items";
		$query_data = array("tag" => $options->tag);
		$response = $db->run($query, $query_data);
		$options->total = $response->rowCount();
		$content = (array) $response->fetchAll();
		
		// Truncate
		foreach ($content as $post):
			$post->text = $this->truncate($post->text, $options->truncate);
		endforeach;
		
		// Render content
		$this->render_content($content, $options);
		
	}
	
}


// Page Setup
$typset = new Typset();

	
?>