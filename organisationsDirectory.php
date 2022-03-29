<?php

# Define a class for obtaining an online directory
require_once ('frontControllerApplication.php');
class organisationsDirectory extends frontControllerApplication
{
	# Function to assign defaults additional to the general application defaults
	public function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			'applicationName'			=> 'Organisations directory',
			'hostname'					=> 'localhost',
			'database'					=> 'organisationsdirectory',
			'username'					=> 'organisationsdirectory',
			'password'					=> NULL,
			'table'						=> 'organisations',
			'div'						=> 'organisationsdirectory',
			'administrators'			=> true,
			'useEditing'				=> true,
			'organisationName'			=> NULL,		// For e-mail signature
			'siteEmail'					=> NULL,
			'theme'						=> NULL,		// E.g. 'polar'
			'truncateTo'				=> 50,
			'queryTerm'					=> 'q',
			'homePageTextIntroduction'	=> false,
			'homePageTextAppended'		=> array (),	// type => text
			'internalAuth'				=> true,
			'tabUlClass'				=> 'tabsflat',
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Class properties
	private $type = NULL;
	
	
	# Function to assign additional actions
	public function actions ()
	{
		# Specify additional actions
		$actions = array (
			'home' => array (
				'description' => false,
				'url' => '',
				'tab' => 'Home',
				'icon' => 'house',
			),
		);
		
		# Add each type
		foreach ($this->types as $type) {
			$actions += array (
				$type . $this->typeNamespaceSuffix => array (
					'description' => ucfirst ($type),
					'url' => $type . '/',
					'tab' => ucfirst ($type),
					'authentication' => false,
				),
			);
		}
		
		# Continue the actions
		$actions += array (
			'country' => array (
				'description' => false,
				'url' => '',
				'usetab' => 'home',
			),
			'search' => array (
				'description' => false,
				'url' => 'search/',
				'icon' => 'magnifier',
			),
			'add' => array (
				'description' => 'Submit a new entry',
				'url' => 'add.html',
				'tab' => 'Add entry',
				'authentication' => true,
				'icon' => 'add',
			),
			'update' => array (
				'description' => 'Update an entry',
				'url' => 'edit/',
				'tab' => 'Update entry',
				'authentication' => true,
				'icon' => 'page_edit',
			),
			'edit' => array (
				'description' => 'Update an entry',
				'url' => 'edit/%1/',
				'usetab' => 'update',
				'authentication' => true,
			),
			'editing' => array (
				'description' => 'Data editing',
				'url' => 'data/',
				'tab' => 'Data editing',
				'icon' => 'pencil',
				'administrator' => true,
			),
			'manager' => array (
				'description' => 'Assign manager of organisation',
				'url' => 'manager/',
				'parent' => 'admin',
				'administrator' => true,
			),
			'export' => array (
				'description' => 'Export data to CSV file',
				'url' => 'data.csv',
				'parent' => 'admin',
				'export' => true,
				'administrator' => true,
			),
		);
		
		# Return the actions
		return $actions;
	}
	
	
	# Database structure definition
	public function databaseStructure ()
	{
		return "
			CREATE TABLE IF NOT EXISTS `administrators` (
			  `username` varchar(191) NOT NULL COMMENT 'Username',
			  `active` enum('','Yes','No') NOT NULL DEFAULT 'Yes' COMMENT 'Currently active?',
			  `privilege` enum('Administrator','Restricted administrator') NOT NULL DEFAULT 'Administrator' COMMENT 'Administrator level',
			  PRIMARY KEY (`username`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System administrators';
			
			CREATE TABLE IF NOT EXISTS `countries` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) NOT NULL,
			  `abbreviatedName` varchar(255) DEFAULT NULL,
			  `suppressed` TINYINT NULL DEFAULT NULL COMMENT 'Results suppressed?'
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			
			CREATE TABLE IF NOT EXISTS `organisations` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique key',
			  `name` varchar(255) NOT NULL COMMENT 'Name',
			  `type` enum('','libraries','museums','organisations') NOT NULL COMMENT 'Type',
			  `englishEquivalent` varchar(255) DEFAULT NULL COMMENT 'English equivalent of name',
			  `countryId` int(4) NOT NULL COMMENT 'Country',
			  `website` varchar(255) DEFAULT NULL COMMENT 'Website',
			  `contactName` varchar(255) DEFAULT NULL COMMENT 'Contact name',
			  `address` text COMMENT 'Address',
			  `email` varchar(255) DEFAULT NULL COMMENT 'E-mail',
			  `telephone` varchar(255) DEFAULT NULL COMMENT 'Telephone',
			  `fax` varchar(255) DEFAULT NULL COMMENT 'Fax',
			  `openToPublic` varchar(255) DEFAULT NULL COMMENT 'Open to the public? / Opening hours',
			  `yearOfFoundation` varchar(255) DEFAULT NULL COMMENT 'Year of foundation',
			  `activities` text COMMENT 'Activities',
			  `collections` text COMMENT 'Collections',
			  `publications` text COMMENT 'Publications',
			  `notes` text COMMENT 'Notes',
			  `unapproved` TINYINT DEFAULT NULL COMMENT 'Hidden? (i.e. unapproved/deleted)',
			  `userId` int(11) DEFAULT NULL COMMENT 'User ID',
			  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			
			CREATE TABLE IF NOT EXISTS `settings` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key (ignored)',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Settings';
			
			CREATE TABLE IF NOT EXISTS `users` (
			  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `email` varchar(191) NOT NULL COMMENT 'Your e-mail address',
			  `password` varchar(255) NOT NULL COMMENT 'Password',
			  `validationToken` varchar(255) DEFAULT NULL COMMENT 'Token for validation or password reset',
			  `lastLoggedInAt` datetime DEFAULT NULL COMMENT 'Last logged in time',
			  `validatedAt` datetime DEFAULT NULL COMMENT 'Time when validated',
			  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp',
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `email` (`email`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Users';
		";
	}
	
	
	# GUI search box
	public function guiSearchBox ()
	{
		return $this->searchBox (true, false);
	}
	
	
	# Additional initialisation, pre-actions
	public function mainPreActions ()
	{
		# Get the supported types
		$this->types = $this->getTypes ();
		
		# If a type is supplied, ensure it is supported
		$this->typeNamespaceSuffix = 'listing';
		$this->typeUrlPart = '';
		if (isSet ($_GET['type'])) {
			if (!in_array ($_GET['type'], $this->types)) {
				echo $this->page404 ();
				return false;
			}
			$this->type = $_GET['type'];
			$this->typeUrlPart = $this->type . '/';
			$this->tabForced = $this->type . $this->typeNamespaceSuffix;
		}
		
	}
	
	
	# Additional initialisation
	public function main ()
	{
		# Assign the country datasource
		$this->countryDatasource = $this->settings['database'] . '.countries';
		
		# Create a registry of countries (across any type)
		if (!$this->countries = $this->getCountries ()) {
			echo "<p class=\"warning\">Error: There was a problem getting the list of countries.</p>";
		}
		
	}
	
	
	# Function to get the supported types
	public function getTypes ()
	{
		# Get the types in use
		$query = "SELECT DISTINCT type FROM {$this->settings['database']}.{$this->settings['table']} ORDER BY type;";
		$types = $this->databaseConnection->getPairs ($query);
		
		# Return the list
		return $types;
	}
	
	
	# Main page
	public function home ($type = false)
	{
		# Start the HTML
		$html  = '';
		
		# Construct the HTML
		if ($this->type) {$html .= "\n<h2>" . ucfirst (htmlspecialchars ($this->type)) . '</h2>';}
		if (!$this->type) {
			$html .= "\n" . $this->settings['homePageTextIntroduction'];
		}
		if (isSet ($this->settings["homePageTextPrepended_{$this->type}Html"]) && strlen ($this->settings["homePageTextPrepended_{$this->type}Html"])) {
			$html .= "\n" . '<div class="graybox">';
			$html .= "\n" . $this->settings["homePageTextPrepended_{$this->type}Html"];
			$html .= "\n" . '</div>';
		}
		$html .= "\n<p>If your " . ($this->type ? application::singularise ($this->type) : 'organisation') . " is not listed, please <a href=\"{$this->baseUrl}/add.html\">submit it for inclusion</a>.</p>";
		$html .= $this->searchBox ();
		$html .= "\n<p>&hellip; or browse by country:</p>";
		$html .= $this->indexListing ();
		if (isSet ($this->settings['homePageTextAppended'][$this->type])) {
			$html .= "\n" . $this->settings['homePageTextAppended'][$this->type];
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to create a listing of countries
	private function indexListing ()
	{
		# Create the listing
		$list = array ();
		$countriesThisType = $this->getCountries ($this->type);
		foreach ($countriesThisType as $abbreviatedName => $name) {
			$list[] = "\n\t<li><a href=\"{$this->baseUrl}/{$this->typeUrlPart}" . htmlspecialchars ($abbreviatedName) . '.html">' . htmlspecialchars ($name) . '</a></li>';
		}
		
		# Compile the HTML
		$html = application::splitListItems ($list, 2, 'countrylisting');
		
		# Return the constructed HTML
		return $html;
	}
	
	
	# Country page
	public function country ($country)
	{
		# Start the HTML
		$html  = '';
		
		# Ensure the country exists somewhere
		if (!isSet ($this->countries[$country])) {
			echo $this->page404 ();
			return $html;
		}
		
		# Construct the HTML
		if ($this->type) {$html .= "\n<h2>" . ucfirst (htmlspecialchars ($this->type)) . '</h2>';}
		
		# Show the jump list
		$html .= $this->jumpForm ($country);
		
		# Show the records
		$html .= $this->showRecords ($country);
		
		# Show the HTML
		echo $html;
	}
	
	
	# Get countries list
	private function getCountries ($type = false)
	{
		# Construct a query
		$query = "
			SELECT
				IF(({$this->countryDatasource}.abbreviatedName = '' OR {$this->countryDatasource}.abbreviatedName IS NULL), LOWER({$this->countryDatasource}.name), {$this->countryDatasource}.abbreviatedName) as moniker,
				{$this->countryDatasource}.name
			FROM {$this->countryDatasource}, {$this->dataSource}
			WHERE {$this->dataSource}.countryId = {$this->countryDatasource}.id"
			. ($type ? " AND type = '{$type}'" : '') . "
			GROUP BY name, abbreviatedName	/* abbreviatedName added as per https://stackoverflow.com/a/34115425/180733 */
			ORDER BY " . $this->databaseConnection->trimSql ($this->countryDatasource . '.name') . "
			;
		";
		
		# Get the data
		$data = $this->databaseConnection->getPairs ($query);
		
		# Return the data
		return $data;
	}
	
	
	# Function to get records
	private function getRecords ($country = false, $fullData = true, $orderBy = 'name', $approvedOnly = true)
	{
		# Construct a query
		$query = '
			SELECT ' . ($fullData ? "{$this->dataSource}.*" : "{$this->dataSource}.id, {$this->dataSource}.name, {$this->countryDatasource}.name AS country") . '
			FROM ' . $this->dataSource . ', ' . $this->countryDatasource . '
			WHERE 1=1 '
			. ($country ? " AND {$this->countryDatasource}.name = '{$country}'": '')
			. ($this->type ? " AND {$this->dataSource}.type = '{$this->type}'": '')
			. " AND " . $this->dataSource . '.countryId = ' . $this->countryDatasource . '.id'
			. ($approvedOnly ? ' AND ' . $this->dataSource . ".unapproved IS NULL" : '')
			. " ORDER BY {$orderBy};";
		
		# Get the data
		$data = $this->databaseConnection->getData ($query, $this->dataSource);
		
		# Return the data
		return $data;
	}
	
	
	# Wrapper function to create a jump form to switch between pages
	private function jumpForm ($country)
	{
		return $html = application::htmlJumplist ($this->countries, $country, $this->baseUrl . '/', $name = 'jumplist', $parentTabLevel = 0, $class = 'jumplist', $introductoryText = "Go to: <a href=\"{$this->baseUrl}/{$this->typeUrlPart}" . "\">Introductory page</a> or country:", $valueSubstitution = "{$this->baseUrl}/{$this->typeUrlPart}%value.html");
	}
	
	
	# Wrapper function to create a jump form to switch between pages
	private function jumpFormInternal ($values)
	{
		# Truncate the values
		foreach ($values as $key => $value) {
			if (mb_strlen ($value) > $this->settings['truncateTo']) {
				$values[$key] = mb_substr ($value, 0, $this->settings['truncateTo']) . ((mb_strlen ($value) > $this->settings['truncateTo']) ? ' ...' : '');
			}
		}
		
		# Return the HTML
		return $html = application::htmlJumplist ($values, '', $this->baseUrl . '/', 'jumplist', $parentTabLevel = 0, $class = 'jumplist', $introductoryText = 'Jump down to:');
	}
	
	
	# Function to create results pages
	private function showRecords ($country)
	{
		# Start the HTML
		$html  = '';
		
		# Check if the country's results should be suppressed
		if ($this->suppressResults ($country)) {
			$html .= "\n<p>Results from this country are currently unavailable.</p>";
			return $html;
		}
		
		# Add tabs to the same country for other types
		$html .= $this->crossCountryTabs ($country);
		
		# Get the countries for this type
		if (!$countriesThisType = $this->getCountries ($this->type)) {
			$html .= "\n<p class=\"warning\">There was a problem getting the list of countries.</p>";
			return $html;
		}
		
		# Ensure it's in the list of countries, or end
		if (!isSet ($countriesThisType[$country])) {
			return $html .= "\n<p>There are no {$this->settings['theme']} {$this->type} in {$this->countries[$country]} in the directory.</p>";
		}
		
		# Get the data
		$data = $this->getRecords ($this->countries[$country]);
		
		# State the number of records
		$totalRecords = count ($data);
		$html .= "\n" . '<p>There ' . ($totalRecords == 1 ? 'is one entry' : "are {$totalRecords} entries") . ' in the ' . ($this->type ? '<strong>' . htmlspecialchars ($this->type) . ' </strong>' : '') . 'directory for <strong>' . htmlspecialchars ($this->countries[$country]) . '</strong>:</p>';
		
		# Remove unwanted fields
		$hideFields = array ('id', 'countryId', 'unapproved', 'timestamp', );
		foreach ($data as $id => $record) {
			foreach ($hideFields as $field) {
				unset ($data[$id][$field]);
			}
		}
		
		# Add a jumplist
		$jumplist = array ();
		foreach ($data as $id => $record) {
			$url = $_SERVER['_PAGE_URL'] . "#id{$id}";
			$jumplist[$url] = $record['name'];
		}
		$html .= $this->jumpFormInternal ($jumplist);
		
		# Make the records entity-safe, allowing only <em></em> tags
		foreach ($data as $id => $record) {
			foreach ($record as $key => $value) {
				$data[$id][$key] = str_replace (array ('&lt;em&gt;', '&lt;/em&gt;'), array ('<em>', '</em>'), htmlspecialchars ($value));
			}
		}
		
		# Loop through each result to create tables
		$records = array ();
		foreach ($data as $id => $record) {
			
			# Start the title
			$title = '';
			
			# Add the title for this item
			$title .= '<h3 id="id' . $id . '">' . $record['name'] . ($record['englishEquivalent'] ? " [{$record['englishEquivalent']}]" : '') . '</h3>';
			unset ($record['name']);
			
			# Add an edit link
			$userIsManager = ($this->user && ($this->userIsAdministrator || ($record['userId'] == $this->user)));
			if ($userIsManager) {
				$title .= '<p class="adminloginlink"><a href="' . $this->baseUrl . '/' . $id . '/edit/"><img src="/images/icons/pencil.png" alt="Edit" /> Edit</a></p>';
			}
			unset ($record['userId']);
			
			# Don't show the type if already in type context
			if ($this->type) {
				unset ($record['type']);
			} else {
				$link = $this->baseUrl . '/' . $record['type'] . '/' . $country . '.html';
				$record['type'] = "<a href=\"{$link}\">" . ucfirst (application::singularise ($record['type'])) . '</a>';
			}
			
			# Apply adjustments to the data
			if ($record['englishEquivalent']) {$record['englishEquivalent'] = '[' . $record['englishEquivalent'] . ']';}
			$record['email'] = application::encodeEmailAddress ($record['email']);
			if (isSet ($record['notes'])) {
				$record['notes'] = application::formatTextBlock ($record['notes']);
			}
			if ($record['website']) {
				$record['website'] = '<a href="' . $record['website'] . '" target="_blank">' . application::urlPresentational ($record['website']) . '</a>';
			}
			
			# Hyperlink URLs in the notes field
			if (isSet ($record['notes'])) {
				$record['notes'] = application::makeClickableLinks ($record['notes']);
			}
			
			# Editing links for admins
			if ($userIsManager) {
				$record['Edit'] = "<a href=\"{$this->baseUrl}/{$id}/edit/\">[Edit]</a>" . ($this->userIsAdministrator ? " | <a href=\"{$this->baseUrl}/{$id}/manager/\">[Assign manager]</a>" : '');
			}
			
			# Register the HTML
			$records[$title] = $record;
		}
		
		# Get the headings
		$headings = $this->databaseConnection->getHeadings ($this->settings['database'], $this->settings['table']);
		$headings['englishEquivalent'] = '';
		
		# Compile the HTML
		foreach ($records as $title => $table) {
			$html .= "\n<div class=\"graybox\">";
			$html .= "\n\n\n\t" . $title;
			$html .= application::htmlTableKeyed ($table, $headings, true, 'lines entries', $allowHtml = true, true, $addRowKeyClasses = true);
			$html .= "\n\t" . '<p><a href="#top"><img align="right" alt="^ Top" border="0" width="29" height="11" src="/images/general/top.gif" /></a></p>';
			$html .= "\n</div>";
		}
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to determine whether the results for a country are suppressed
	private function suppressResults ($country)
	{
		# Get the country ID
		$countryId = $this->countryIdFromMoniker ($country);
		
		# Determine any match
		$conditions = array ('id' => $countryId, 'suppressed' => '1');
		$match = $this->databaseConnection->select ($this->settings['database'], 'countries', $conditions);
		
		# Return a match
		return ($match);
	}
	
	
	# Function to add type filtering links for the search results page
	private function crossCountryTabs ($country)
	{
		# Get the country ID
		$countryId = $this->countryIdFromMoniker ($country);
		
		# Get counts for each type
		$query = "SELECT type, COUNT(*) AS total from {$this->settings['table']} WHERE countryId = {$countryId} GROUP BY type
			UNION
			SELECT '' AS type, COUNT(*) AS total FROM {$this->settings['table']} WHERE countryId = {$countryId}
		;";
		$countsByType = $this->databaseConnection->getPairs ($query);
		
		# Construct the list of links
		$links = array ();
		$links['filter'] = ucfirst (htmlspecialchars ($country)) . ':';
		$links[''] = '<a href="' . $this->baseUrl . '/' . $country . ".html\">Show all ({$countsByType['']})</a>";
		foreach ($this->types as $type) {
			if (!isSet ($countsByType[$type])) {$countsByType[$type] = 0;}
			$links[$type] = '<a href="' . $this->baseUrl . '/' . $type . '/' . $country . '.html">' . ucfirst ($type) . " ({$countsByType[$type]})</a>";
		}
		
		# Convert to tabs
		$html = application::htmlUl ($links, 0, $className = 'tabs subtabs filtering', true, false, false, false, $this->type);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to list the user's current entries
	private function mine ()
	{
		# Get the user's entries
		$data = $this->databaseConnection->select ($this->settings['database'], $this->settings['table'], array ('userId' => $this->user));
		
		# End if none
		if (!$data) {
			return "\n<p>You do not appear to be the manager of any entries at present. If you think you should have edit rights, please request editing rights below:</p>";
		}
		
		# Compile as a list
		$list = array ();
		foreach ($data as $id => $organisation) {
			$list[] = "<a href=\"{$this->baseUrl}/{$id}/edit/\"><strong>" . '<img src="/images/icons/pencil.png" alt="Edit" border="0" /> ' . htmlspecialchars ($organisation['name']) . '</strong></a>' . ($organisation['unapproved'] ? ' <span class="comment">[not yet approved]</span>' : '');
		}
		$html = application::htmlUl ($list, 0, 'boxylist');
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to request to become the manager of an organisation's entry
	public function update ()
	{
		# Text for people with a password already
		$html  = "\n<h3>My entries</h3>";
		$html .= $this->mine ();
		
		# Headings
		$html .= "\n<h3>Request to become the manager of an organisation's entry</h3>";
		$html .= "\n<p>Use this form to request rights to edit an entry.</p>";
		$html .= "\n<p class=\"comment\">N.B.: Requests will be checked against our existing records.</p>";
		
		# Get the list of entries
		$dataRaw = $this->getRecords (false, false, 'country,name');
		
		# Convert to lists (grouped by country) of key/value pairs
		$data = array ();
		foreach ($dataRaw as $id => $record) {
			$country = $record['country'];
			$data[$country][$id] = $record['name'];
		}
		
		# Get the number being requested
		#!# ultimateForm needs support for placeholders in emailIntroductoryText
		$number = (isSet ($_POST['form']) ? $_POST['form']['entry'] : false);
		$name = (isSet ($_POST['form']) ? urlencode ($_POST['form']['name']) : false);
		$email = (isSet ($_POST['form']) ? urlencode ($_POST['form']['email']) : false);
		
		# Create the form
		require_once ('ultimateForm.php');
		$form = new form (array (
			'displayRestrictions' => false,
			'formCompleteText' => 'Thank you for your request. We will be in touch shortly.',
			'div' => 'graybox',
			'emailIntroductoryText' => "Please respond to the request below by submitting the form at\n{$_SERVER['_SITE_URL']}{$this->baseUrl}/{$number}/manager/?name={$name}&email={$email}\n\n---",
		));
		$form->select (array (
			'name'			=> 'entry',
			'title'			=> 'Entry (organised by country then name)',
			'values'		=> $data,
			'required'		=> true,
			'truncate'		=> $this->settings['truncateTo'],
		));
		$form->input (array (
			'name'			=> 'name',
			'title'			=> 'Your name',
			'required'		=> true,
		));
		$form->email (array (
			'name'			=> 'email',
			'title'			=> 'E-mail',
			'default'		=> $this->userVisibleIdentifier,
			'editable'		=> ($this->userIsAdministrator),
			'required'		=> true,
		));
		$form->input (array (
			'name'			=> 'who',
			'title'			=> 'Give details of your role in the organisation',
			'required'		=> true,
		));
		$form->setOutputEmail ($this->settings['feedbackRecipient'], $this->settings['siteEmail'], 'Manager application for ' . $this->settings['applicationName'], NULL, 'email');
		$form->process ($html);
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to assign a user as a manager of an organisation
	public function manager ()
	{
		# Get the list of entries
		$dataRaw = $this->getRecords (false, false, 'country,name', $approvedOnly = false);
		
		# Check for a supplied ID number
		$recordId = (isSet ($_GET['id']) && ctype_digit ($_GET['id']) && isSet ($dataRaw[$_GET['id']]) ? $_GET['id'] : false);
		
		# Headings
		$html  = "\n<p>Use this form to assign a manager to " . ($recordId ? 'this' : 'an') . ' organisation. An e-mail will automatically be sent to the address specified, giving details.</p>';
		
		# Regroup as a multi-dimensional array, by country
		$data = application::regroup ($dataRaw, $regroupByColumn = 'country', $removeGroupColumn = true);
		
		# Get the list of users
		$users = $this->databaseConnection->selectPairs ($this->settings['database'], 'users', array (), array ('id', 'email'), true, $orderBy = 'email');
		
		# Convert each country's records to a simpler a => b array rather than a => a, b, truncating if necessary
		foreach ($data as $country => $records) {
			foreach ($records as $id => $record) {
				$data[$country][$id] = $record['name'];
			}
		}
		
		# Create the form
		require_once ('ultimateForm.php');
		$form = new form (array (
			'displayRestrictions' => false,
			'formCompleteText' => false,
			'div' => 'graybox',
			'autofocus' => true,
		));
		$form->select (array (
			'name'			=> 'id',
			'title'			=> 'Entry' . ($recordId ? '' : ' (organised by country then name)'),
			'values'		=> $data,
			'required'		=> true,
			'truncate'		=> ($recordId ? false : $this->settings['truncateTo']),
			'default'		=> $recordId,
			'editable'		=> (!$recordId),
		));
		
		# Show the current manager
		$requestedEmail = (isSet ($_GET['email']) ? array_search ($_GET['email'], $users) : false);
		if ($recordId) {
			$record = $this->databaseConnection->selectOne ($this->settings['database'], $this->settings['table'], array ('id' => $recordId));
			if ($record['userId']) {
				$manager = $this->databaseConnection->selectOne ($this->settings['database'], 'users', array ('id' => $record['userId']));
				$managerEmail = $manager['email'];
				if ($requestedEmail && ($managerEmail == $_GET['email'])) {$managerEmail .= ' [Same]';}
				$form->input (array (
					'name'		=> 'currentmanager',
					'title'		=> 'Current manager',
					'default'	=> $managerEmail,
					'discard'	=> true,
					'editable'	=> false,
				));
			}
		}
		
		$form->input (array (
			'name'			=> 'name',
			'title'			=> 'Name of requester',
			'required'		=> true,
			'default'		=> (isSet ($_GET['name']) ? $_GET['name'] : false),
		));
		$form->select (array (
			'name'			=> 'email',
			'title'			=> 'E-mail of requester',
			'required'		=> true,
			'default'		=> $requestedEmail,
			'values'		=> $users,
		));
		$form->textarea (array (
			'name'			=> 'request',
			'title'			=> 'Original email from requester (if any)',
			'cols'			=> 81,
			'rows'			=> 7,
		));
		if ($result = $form->process ($html)) {
			
			# Extract the name of the entry
			$nameOfEntry = $dataRaw[$result['id']]['name'];
			
			# Insert the user
			$data = array ('userId' => $result['email']);
			if (!$this->databaseConnection->update ($this->settings['database'], $this->settings['table'], $data, array ('id' => $result['id']))) {
				$html .= "<p class=\"warning\">There was a problem updating the data. No e-mail has been sent.</p>";
				return $html;
			}
			
			# Construct the e-mail
			$message  = "\nDear {$result['name']},\n";
			if ($result['request']) {
				$message .= "\nThank you for your e-mail regarding updating an entry in the {$this->settings['applicationName']}. You now have access as follows:\n";
			} else {
				$message .= "\nYou now have access to update an entry in the {$this->settings['applicationName']}:\n";
			}
			$message .= "\n" . str_repeat ('-', 74);
			$message .= "\nOrganisation:  {$nameOfEntry}";
			$message .= "\nEdit at:       {$_SERVER['_SITE_URL']}{$this->baseUrl}/{$result['id']}/edit/";
			$message .= "\n" . str_repeat ('-', 74) . "\n";
			$message .= "\n** Please ensure you save these details. **\n";
			$message .= "\nThank you for helping keep the directory up-to-date.";
			$message .= "\nIf you have any questions, just reply to this e-mail.\n";
			$message .= "\nBest wishes,\n\n\n{$this->settings['organisationName']}.\n";
			if ($result['request']) {
				$message .= "\n\n" . application::emailQuoting ($result['request']);
			}
			
			# Prepare message parameters
			$email = $users[$result['email']];
			$to = "{$result['name']} <{$email}>";
			$subject = "{$this->settings['applicationName']} - manager of entry";
			$message = wordwrap ($message);
			$from = "{$this->settings['organisationName']} <{$this->settings['siteEmail']}>";
			$extraHeaders  = "From: {$from}";
			$extraHeaders .= "\r\nBcc: " . (is_array ($this->settings['feedbackRecipient']) ? implode (', ', $this->settings['feedbackRecipient']) : $this->settings['feedbackRecipient']);
			
			# Send the message
			application::utf8Mail ($to, $subject, $message, $extraHeaders);
			
			# Confirm sending
			$html .= application::showMail ($to, $subject, $message, $extraHeaders, $prefix = 'The user has now been allocated to this entry, and an e-mail sent, as follows:');
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to edit an item
	public function edit ($id)
	{
		# Show the editing form if requested
		$id = ((isSet ($_GET['id']) && ctype_digit ($_GET['id'])) ? $_GET['id'] : false);
		
		# Get the data for the supplied ID or end
		if (!$id || !$record = $this->databaseConnection->selectOne ($this->settings['database'], $this->settings['table'], array ('id' => $id))) {
			echo "\n<p>There is no such record. Please check the URL and try again.</p>";
			application::sendHeader (404);
			return false;
		}
		
		# Hand off to the record manipulation function
		return $this->manipulate ($record);
	}
	
	
	# Function to add an item
	public function add ()
	{
		return $this->manipulate ();
	}
	
	
	# Function to provide a search facility
	public function search ()
	{
		# Start the HTML
		$html  = "\n<h2>Search" . ($this->type ? ' - ' . htmlspecialchars ($this->type) : '') . '</h2>';
		
		# Heading and form
		$html .= "<div class=\"graybox\">";
		$html .= $this->searchBox ();
		$html .= "</div>\n<br />";
		
		# Create the results if a query is supplied
		if ($query = (isSet ($_GET[$this->settings['queryTerm']]) ? trim ($_GET[$this->settings['queryTerm']]) : '')) {
			$html .= $this->searchResults ($query);
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to provide search results
	private function searchResults ($searchPhrase)
	{
		# Prevent listing of all records
		if (strlen ($searchPhrase) < 3) {
			return $html = "\n<p>The search phrase must be at least three characters long.</p>";
		}
		
		# Construct a query
		$query = "SELECT
			{$this->settings['table']}.id,
			CONCAT( '{$this->baseUrl}/' , IF(abbreviatedName>'',abbreviatedName,LOWER(countries.name)) , '.html#id' , {$this->settings['table']}.id) AS url,
			{$this->settings['table']}.name,
			countries.name AS country,
			type
		FROM {$this->settings['database']}.{$this->settings['table']}
		LEFT JOIN {$this->settings['database']}.countries ON countryId = countries.id
		WHERE
			unapproved IS NULL
			AND
				(
				   {$this->settings['table']}.name LIKE :searchPhrase
				OR englishEquivalent LIKE :searchPhrase
				OR address LIKE :searchPhrase
				OR publications LIKE :searchPhrase"
				. ($this->settings['table'] != 'museums' ? " OR activities LIKE :searchPhrase" : '')
				. ($this->settings['table'] != 'museums' ? " OR notes LIKE :searchPhrase" : '')
				. ')'
		. ';';
		
		# End if no results
		if (!$data = $this->databaseConnection->getData ($query, "{$this->settings['database']}.{$this->settings['table']}", true, array ('searchPhrase' => '%' . $searchPhrase . '%'))) {
			return $html = "\n<p>Sorry, no items were found.</p>";
		}
		
		# Determine what data to use
		$dataByType = application::regroup ($data, 'type');
		$results = ($this->type ? (isSet ($dataByType[$this->type]) ? $dataByType[$this->type] : array ()) : $data);
		
		# Convert to a list
		$list = array ();
		foreach ($results as $key => $record) {
			$list[$key] = "<a href=\"{$record['url']}\">" . htmlspecialchars ("{$record['name']} ({$record['country']})") . "</a>";
		}
		
		# Show the HTML
		$records = count ($list);
		$typeText = ($this->type ? htmlspecialchars (application::singularise ($this->type)) . ' ' : '');
		$html  = "\n<p>Matching text for '<strong>" . htmlspecialchars ($searchPhrase) . "</strong>' was " . ($records ? "found in <strong>" . ($records == 1 ? "one {$typeText}entry" : "{$records} {$typeText}entries") . '</strong>' : "not found in any {$typeText}entries") . ':</p>';
		$html .= $this->searchTypeFilterLinks ($searchPhrase, $data, $dataByType);
		if ($records) {
			$html .= application::htmlUl ($list);
		} else {
			$html .= "\n<p>Sorry, no items were found.</p>";
		}
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to add type filtering links for the search results page
	private function searchTypeFilterLinks ($searchPhrase, $data, $dataByType)
	{
		# Get counts for each type
		$countsByType = array ();
		$countsByType[''] = count ($data);	// All
		foreach ($this->types as $type) {
			$countsByType[$type] = (isSet ($dataByType[$type]) ? count ($dataByType[$type]) : 0);
		}
		
		# Construct the list of links
		$links = array ();
		$links['filter'] = 'Filtering:';
		$links[''] = '<a href="' . $this->searchTargetUrl (false, $searchPhrase) . "\">Show all ({$countsByType['']})</a>";
		foreach ($this->types as $type) {
			$links[$type] = '<a href="' . $this->searchTargetUrl ($type, $searchPhrase) . '">' . ucfirst ($type) . " ({$countsByType[$type]})</a>";
		}
		
		# Convert to tabs
		$html = application::htmlUl ($links, 0, $className = 'tabs subtabs filtering', true, false, false, false, $this->type);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to generate a URL for a search
	private function searchTargetUrl ($type, $searchPhrase = false)
	{
		return $this->baseUrl . '/' . ($type ? $type . '/' : '') . 'search/' . ($searchPhrase ? '?q=' . htmlspecialchars (urlencode ($searchPhrase)) : '');
	}
	
	
	# Function to create the search box
	private function searchBox ($minisearch = false, $autofocus = true)
	{
		$query = (isSet ($_GET[$this->settings['queryTerm']]) ? trim ($_GET[$this->settings['queryTerm']]) : false);
		$target = $this->searchTargetUrl ($this->type);
		return "\n\n" . '<form method="get" action="' . $target . '" class="' . ($minisearch ? 'minisearch' : 'search') . '" name="' . ($minisearch ? 'minisearch' : 'search') . '">
			<img src="/images/icons/magnifier.png" alt="" class="icon">
			<input class="searchbox" name="' . $this->settings['queryTerm'] . '" type="search" size="' . ($minisearch ? '20' : '35') . '" value="' . $query . '" placeholder="Search ' . ($this->type ? htmlspecialchars ($this->type) : 'the directory') . '"' . ($autofocus ? ' autofocus="autofocus"' : '') . ' />
			<input value="Search!" accesskey="s" type="submit" class="button" />
		</form>' . "\n";
	}
	
	
	# Function to manipulate data
	private function manipulate ($record = false)
	{
		# Start the HTML
		$html = '';
		
		# Ensure this is the current user's entry
		if ($record) {
			if (!$this->userIsAdministrator) {
				if ($this->user != $record['userId']) {
					echo "\n<p class=\"warning\">This does not appear to be your entry. If you think you should have edit rights, please <a href=\"{$this->baseUrl}/update.html\">request editing rights</a> for this entry.</p>";
					return false;
				}
			}
		}
		
		# Introductory text
		if ($record) {
			$html .= "\n<p>As " . ($this->user == $record['userId'] ? "the manager of this organisation's entry" : 'an Administrator') . ", you can use this form to edit the entry.</p>";
		} else {
			$html .= "\n<p>You can use the form below to add an entry to the Directory. Please leave blank those fields which do not apply.</p>\n<p>Before submitting an entry, <a href=\"{$this->baseUrl}/\">please kindly firstly check</a> whether it already exists.</p>";
			$html .= "\n<p><em>All submissions are checked and subject to approval before being made live. Spammy submissions will not be added.</em></p>";
		}
		
		# Create the form
		require_once ('ultimateForm.php');
		$form = new form (array (
			'displayRestrictions' => false,
			'formCompleteText' => false,
			'databaseConnection' => $this->databaseConnection,
			'antispam'	=> true,
			'emailIntroductoryText' => ($record ? 'This entry has been updated by its manager:' : "A new submission has been made. Please review it at\n{$_SERVER['_SITE_URL']}{$this->baseUrl}/data/organisations/"),
			'rows' => 7,
			'cols' => 50,
			'unsavedDataProtection' => true,
		));
		
		# Generate the form fields
		$exclude = array ('id', 'timestamp');
		if (!$this->userIsAdministrator) {	// Admins can view/change the userId and set the approval
			$exclude[] = 'userId';
			$exclude[] = 'unapproved';
		}
		$form->dataBinding (array (
			'database' => $this->settings['database'],
			'table' => $this->settings['table'],
			'lookupFunction' => array ('database', 'lookup'),
			'simpleJoin' => true,
			'lookupFunctionParameters' => array (NULL, false, true, false, $firstOnly = true),
			'exclude' => $exclude,
			'int1ToCheckbox' => true,
			'data' => $record,
			'attributes' => array (
				'type' => array ('valuesNamesAutomatic' => true, ),
				'website' => array ('regexp' => '^http://', 'description' => 'Must begin http://'),
				'email' => array ('description' => 'E-mail addresses on the site will appear obfuscated, to prevent harvesting by spammers.'),
				'name' => array ('description' => "The organisation's name, in its native language where appropriate."),
				'englishEquivalent' => array ('description' => "If the organisation's main name is in a non-English language, the English translation should be specified here."),
				'telephone' => array ('description' => 'Please ensure this is in an international format, i.e. starting with +'),
		)));
		
		# Ensure certain values are different, as an anti- webform-spam measure
		$form->validation ('different', array ('name', 'telephone', 'yearOfFoundation', ));
		$form->validation ('different', array ('address', /* 'collections', */ 'publications', ));
		
		# Output options
		$form->setOutputScreen (false);
		$form->setOutputEmail ($this->settings['feedbackRecipient'], $this->settings['siteEmail'], $this->settings['applicationName'] . ' ' . ($record ? 'update' : 'submission'));
		
		# Process the form or end
		if (!$result = $form->process ($html)) {
			echo $html;
			return false;
		}
		
		# Add the current user ID to the record; if the user is an Administrator, maintain any current userId of an existing record
		$result['userId'] = ($record && $record['userId'] ? $record['userId'] : ($this->userIsAdministrator ? NULL : $this->user));
		
		# Update/insert the data into the database
		if ($record) {
			$this->databaseConnection->update ($this->settings['database'], $this->settings['table'], $result, array ('id' => $record['id']));
		} else {
			$result['unapproved'] = 1;	// Unapproved initially
			$this->databaseConnection->insert ($this->settings['database'], $this->settings['table'], $result);
		}
		
		# Confirm success
		if ($record) {
			$countryUrlSlug = $this->countryMonikerFromId ($result['countryId']);
			$html  = "<p>Thank you for keeping the Directory up-to-date.</p>";
			$html .= "<p><a href=\"{$this->baseUrl}/{$result['type']}/{$countryUrlSlug}.html#id{$record['id']}\"><img src=\"/images/icons/bullet_go.png\" alt=\"&gt;\" class=\"icon\"> View the updated entry.</a></p>";
		} else {
			$html  = "<p><img src=\"/images/icons/tick.png\" alt=\"\" class=\"icon\"> <strong>Thank you for adding your entry - we'll review the entry and let you know when it's been added.</strong></p>";
		}
		
		# Echo the HTML
		echo $html;
	}
	
	
	# Function to get the country URL moniker from a country ID
	private function countryMonikerFromId ($id)
	{
		#!# This is a bit naff - first we get the name from the number, then search for the name
		$country = $this->databaseConnection->selectOne ($this->settings['database'], 'countries', array ('id' => $id));
		$countryUrlSlug = array_search ($country['name'], $this->countries);
		return $countryUrlSlug;
	}
	
	
	# Function to get the country ID from a country moniker
	private function countryIdFromMoniker ($country)
	{
		#!# This is a bit naff - first we get the name from the number, then search for the name
		$countryName = $this->countries[$country];
		$country = $this->databaseConnection->selectOne ($this->settings['database'], 'countries', array ('name' => $countryName));
		$countryId = $country['id'];
		return $countryId;
	}
	
	
	# Function to export data
	public function export ()
	{
		# Get the data
		$data = $this->getRecords ();
		
		# Set the header labels
		$headerLabels = $this->databaseConnection->getHeadings ($this->settings['database'], $this->settings['table']);
		
		# Serve the CSV
		require_once ('csv.php');
		csv::serve ($data, $filenameBase = 'organisationsdata', $timestamp = true, $headerLabels);
	}
	
	
	# Settings
	public function settings ($dataBindingSettingsOverrides = array ())
	{
		#!# Need to be able to convert the field skipPreviousDays to a single checkbox as per int1ToCheckbox, but not other fields
		
		# Define overrides
		$dataBindingSettingsOverrides = array (
			'attributes' => array (
				'homePageTextPrepended_museumsHtml' => array ('editorToolbarSet' => 'BasicLonger', 'width' => 600, 'height' => 100, ),
			),
		);
		
		# Run the main settings system with the overriden attributes
		return parent::settings ($dataBindingSettingsOverrides);
	}
	
	
	# Admin editing section, substantially delegated to the sinenomine editing component
	public function editing ($attributes = array (), $deny = false, $sinenomineExtraSettings = array ())
	{
		# Define sinenomine settings
		$sinenomineExtraSettings = array (
			'simpleJoin' => true,
			'int1ToCheckbox' => true,
			'orderby' => array ('organisations' => 'id DESC'),		// Most recent first, as most likely to be needing review
		);
		
		# Define tables to deny editing for
		$deny[$this->settings['database']] = array (
			'administrators',
			'settings',
			'users',
		);
		
		# Delegate to the default editor, which will echo the HTML
		parent::editing ($attributes = array (), $deny, $sinenomineExtraSettings);
	}
}

?>
