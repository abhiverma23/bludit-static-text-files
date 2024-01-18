<?php

class pluginStaticTextFile extends Plugin {

	public function init()
	{
	    // JSON database
		$jsondb = json_encode(array(
			'Ads.txt'=>'google.com, pub-0000000000000000, DIRECT, x0x0x0x0x0x0x0x0',
			'ads.txt'=>'google.com, pub-0000000000000000, DIRECT, x0x0x0x0x0x0x0x0'
		));

		// Fields and default values for the database of this plugin
		$this->dbFields = array(
			'jsondb'=>$jsondb
		);

		// Disable default Save and Cancel button
		$this->formButtons = false;
	}
	
	// Method called when a POST request is sent
	public function post()
	{
		// Get current jsondb value from database
		// All data stored in the database is html encoded
		$jsondb = $this->db['jsondb'];
		$jsondb = Sanitize::htmlDecode($jsondb);

		// Convert JSON to Array
		$txtFiles = json_decode($jsondb, true);

		// Check if the user click on the button delete or add
		if( isset($_POST['deleteFile']) ) {
			// Values from $_POST
			$name = $_POST['deleteFile'];

			// Delete the file from the array
			unset($txtFiles[$name]);
		}
		elseif( isset($_POST['addFile']) ) {
			// Values from $_POST
			$name = $_POST['fileName'];
			$content = $_POST['textContent'];

			// Check empty string
			if( empty($name) ) { return false; }

			// Add the text file
			$txtFiles[$name] = $content;
		}

		$this->db['jsondb'] = Sanitize::html(json_encode($txtFiles));

		// Save the database
		return $this->save();
	}
	
	// Method called on plugin settings on the admin area
	public function form()
	{
		global $L;
		
		$html .= '<div>';
		$html .= '<label>'.$L->g('Text file name').'</label>';
		$html .= '<input type="text" name="fileName" placeholder="Ads.txt"/>';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<label>'.DOMAIN.'/'.$this->getValue('route').'</label>';
		$html .= '<textarea name="textContent" placeholder="Content of text file..."></textarea>';
		$html .= '</div>';

		$html  = '<div class="alert alert-primary" role="alert">';
		$html .= $this->description();
		$html .= '</div>';

		// New text file, when the user click on save button this call the method post()
		// and the new text file is added to the database
		$html .= '<h4 class="mt-3">'.$L->get('Add a new text file').'</h4>';

		$html .= '<div>';
		$html .= '<label>'.$L->get('Text file path').'</label>';
		$html .= '<input name="fileName" type="text" class="form-control" placeholder="Ads.txt">';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<label>'.$L->get('File content').'</label>';
		$html .= '<textarea name="textContent" class="form-control" placeholder="Content of text file..."></textarea>';
		$html .= '</div>';

		$html .= '<div>';
		$html .= '<button name="addFile" class="btn btn-primary my-2" type="submit">'.$L->get('Add file').'</button>';
		$html .= '</div>';

		// Get the JSON DB, getValue() with the option unsanitized HTML code
		$jsondb = $this->getValue('jsondb', $unsanitized=false);
		$files = json_decode($jsondb, true);

		$html .= !empty($files) ? '<h4 class="mt-3">'.$L->get('Files').'</h4>' : '';

		foreach($files as $name=>$content) {
		    $html .= '<hr/>';
			$html .= '<div class="my-2">';
			$html .= '<label><a href="'.DOMAIN.'/'.$name.'" target="_blank">'.$name.' <span class="fa fa-external-link"></span></a></label>';
			$html .= '<textarea class="form-control" disabled>'.$content.'</textarea>';
			$html .= '</div>';

			$html .= '<div>';
			$html .= '<button name="deleteFile" class="btn btn-secondary my-2" type="submit" value="'.$name.'">'.$L->get('Delete').'</button>';
			$html .= '</div>';
		}

		return $html;
	}

    //Show content on domain
	public function beforeAll()
	{
	    $jsondb = $this->getValue('jsondb', $unsanitized=false);
		$files = json_decode($jsondb, true);
		
		foreach($files as $webhook=>$content) {
            //Compare the route and display the file is matches file path
		    if ($this->webhook($webhook)) {
    			header('Content-type: text/plain');
    			echo $content;
    			exit(0);
    		}
		}
		
	}

}
