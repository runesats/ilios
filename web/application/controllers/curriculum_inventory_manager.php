<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'ilios_web_controller.php';

/**
 * @package Ilios
 * Curriculum Inventory management controller.
 *
 * @todo hideously fat controller, move business logic to helper/workflow/whatever components.
 */
class Curriculum_Inventory_Manager extends Ilios_Web_Controller
{
    /**
     * Constructor.
     */
    public function __construct ()
    {
        parent::__construct();
        $this->load->model('Course_Clerkship_Type', 'clerkshipType', true);
        $this->load->model('Curriculum_Inventory', 'inventory', true);
        $this->load->model('Curriculum_Inventory_Report', 'invReport', true);
        $this->load->model('Curriculum_Inventory_Academic_Level', 'invAcademicLevel', true);
        $this->load->model('Curriculum_Inventory_Institution', 'invInstitution', true);
        $this->load->model('Curriculum_Inventory_Sequence', 'invSequence', true);
        $this->load->model('Curriculum_Inventory_Sequence_Block', 'invSequenceBlock', true);
    }

    /**
     * Default action.
     * Lists existing reports for the currently active school.
     */
    public function index ()
    {
        $lang = $this->getLangToUse();

        $data = array();
        $data['lang'] = $lang;
        $data['i18n'] =  $this->languagemap;
        $data['institution_name'] = $this->config->item('ilios_institution_name');
        $data['user_id'] = $this->session->userdata('uid');

        // authorization check
        if (! $this->session->userdata('has_admin_access')) {
            $this->_viewAccessForbiddenPage($lang, $data);
            return;
        }

        $schoolId = $this->session->userdata('school_id');
        $schoolRow = $this->school->getRowForPrimaryKeyId($schoolId);

        if (! isset($schoolRow)) {
            show_error('Failed to load school data for this user session.');
            return;
        }

        $this->load->view('curriculum_inventory/index', $data);
    }

    /**
     * Prints the curriculum inventory manager for a requested report.
     * Expects the following input in the request parameter string:
     *    'report_id' ... the report id.
     */
    public function view ()
    {
        $lang = $this->getLangToUse();

        $data = array();
        $data['lang'] = $lang;
        $data['i18n'] =  $this->languagemap;
        $data['institution_name'] = $this->config->item('ilios_institution_name');
        $data['user_id'] = $this->session->userdata('uid');

        // authorization check
        if (! $this->session->userdata('has_admin_access')) {
            $this->_viewAccessForbiddenPage($lang, $data);
            return;
        }

        $schoolId = $this->session->userdata('school_id');
        $schoolRow = $this->school->getRowForPrimaryKeyId($schoolId);

        if (! isset($schoolRow)) {
            show_error('Failed to load school data for this user session.');
            return;
        }

        $data['school_id'] = $schoolId;
        $data['school_name'] = $schoolRow->title;
        $key = 'general.phrases.school_of';
        $schoolOfStr = $this->languagemap->getI18NString($key, $lang);
        $data['viewbar_title'] = $data['institution_name'] . ' - ' . $schoolOfStr . ' ' . $schoolRow->title;

        //
        // load curriculum inventory program for the given program year
        //

        // @todo

        $this->load->view('curriculum_inventory/view', $data);
    }

    /**
     * Creates a new curriculum inventory report for a given academic year and program.
     * Expects the following input to be POSTed:
     *    'year' ... the academic year
     *    'name' ... the report name
     *    'description' ... the report description
     *    'program_id' ... the program id
     * On successful creation, the user will be redirected to view the new report.
     * Any failure will cause an error page to be rendered.
     */
    public function add ()
    {
        $lang = $this->getLangToUse();

        $data = array();
        $data['lang'] = $lang;
        $data['i18n'] =  $this->languagemap;
        $data['institution_name'] = $this->config->item('ilios_institution_name');
        $data['user_id'] = $this->session->userdata('uid');

        // authorization check
        if (! $this->session->userdata('has_admin_access')) {
            $this->_viewAccessForbiddenPage($lang, $data);
            return;
        }

        $schoolId = $this->session->userdata('school_id');
        $schoolRow = $this->school->getRowForPrimaryKeyId($schoolId);

        if (! isset($schoolRow)) {
            show_error('Failed to load school data for this user session.');
            return;
        }

        $year = (int) $this->input->post('year');
        $reportName = $this->input->post('name');
        $reportDescription = $this->input->post('description');
        $programId = (int) $this->input->post('program_id');
        //
        // create new inventory report for the given academic year
        //

        // check if a curriculum inventory report already exists
        $invReport = $this->invReport->getByAcademicYearAndProgram($year, $programId);
        if (isset($invReport)) {
            show_error('A curriculum inventory report already exists for the given academic year and program.');
            return;
        }

        $startYear = $year;
        $endYear = $startYear + 1;
        // create start/end date for the report
        // @todo make hardwired start/end day/month configurable
        $startDate = new DateTime();
        $startDate->setDate($startYear, 7, 1);
        $endDate = new DateTime();
        $endDate->setDate($endYear, 6, 30);

        // create a new curriculum inventory report and associated entities (academic levels, sequence etc.)
        // @todo running db transactions in the controller - BAD! refactor this out.
        $this->db->trans_start();
        $reportId = $this->invReport->create($year, $schoolId, $reportName, $reportDescription, $startDate, $endDate);
        $this->invAcademicLevel->createDefaultLevels($reportId);
        $this->invSequence->create($reportId);
        $this->db->trans_complete();
        if (false === $this->db->trans_status()) {
            show_error('Failed to create curriculum inventory report.');
            return;
        }

        // success! redirect to view the new program
        redirect('curriculum_inventory_manager/view?report_id=' . $reportId);
    }

    /**
     * Prints a requested curriculum inventory report as HTML document.
     * Expects the following input in the request parameter string:
     *    'report_id' ... the report id.
     */
    public function preview ()
    {
        $lang = $this->getLangToUse();

        $data = array();
        $data['lang'] = $lang;
        $data['i18n'] =  $this->languagemap;
        $data['institution_name'] = $this->config->item('ilios_institution_name');
        $data['user_id'] = $this->session->userdata('uid');

        // authorization check
        if (! $this->session->userdata('has_admin_access')) {
            $this->_viewAccessForbiddenPage($lang, $data);
            return;
        }
        // input validation
        $reportId = (int) $this->input->get('report_id');
        if (0 >= $reportId) {
            show_error('Missing or invalid report id.');
            return;
        }

        try {
            $inventory = $this->_loadCurriculumInventory($reportId);
        } catch (Ilios_Exception $e) {
            log_message('error',  'CIM export: ' . $e->getMessage());
            show_error('An error occurred while loading the curriculum inventory.');
            return;
        }

        $data['inventory'] = $inventory;
        $this->load->view('curriculum_inventory/preview', $data);
    }

    /**
     * Prints a requested curriculum inventory report as XML document.
     * Expects the following input in the request parameter string:
     *    'report_id' ... the report id
     */
    public function export ()
    {
        $lang = $this->getLangToUse();

        $data = array();
        $data['lang'] = $lang;
        $data['i18n'] =  $this->languagemap;
        $data['institution_name'] = $this->config->item('ilios_institution_name');
        $data['user_id'] = $this->session->userdata('uid');

        // authorization check
        if (! $this->session->userdata('has_admin_access')) {
            $this->_viewAccessForbiddenPage($lang, $data);
            return;
        }

        // input validation
        $reportId = (int) $this->input->get('report_id');
        if (0 >= $reportId) {
            show_error('Missing or invalid report id.');
            return;
        }

        try {
            $xml = $this->_getXmlReport($reportId);
        } catch (DomException $e) {
            log_message('error',  'CIM export: ' . $e->getMessage());
            show_error('An error occurred while exporting the curriculum inventory report to XML.');
            return;
        } catch (Ilios_Exception $e) {
            log_message('error',  'CIM export: ' . $e->getMessage());
            show_error('An error occurred while loading the curriculum inventory.');
            return;
        }

        $out = $xml->saveXML();
        if (false === $out) {
            log_message('error', 'CIM export: Failed to convert XML to its String representation.');
            show_error('An error occurred while exporting the curriculum inventory report.');

        }
        // all is good, output the XML
        header("Content-Type: application/xml");
        echo $out;
    }

    //
    // XML export functionality
    // @todo move this into a "CodeIgniter library" component
    //

    /**
     * Retrieves the inventory report as XML for a given report id.
     * @param int $reportId the report id
     * @return DomDocument the XML report, or FALSE on failure
     * @throws DomException
     * @throws Ilios_Exception
     */
    protected function _getXmlReport ($reportId)
    {
        // load the inventory from the db and create xml from it.
        // @todo conditionally, load the xml from file (or perhaps the database?) for finalized inventories.
        $inventory = $this->_loadCurriculumInventory($reportId);
        return $this->_createXmlReport($inventory);
    }


    /**
     * Creates an XML representation of the given curriculum inventory.
     * @param array $inventory a nested assoc. array structure containing the inventory data to be
     * @return DOMDocument the generated XML document
     * @throws DomException
     */
    protected function _createXmlReport (array $inventory)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $rootNode = $dom->createElementNS('http://ns.medbiq.org/curriculuminventory/v1/', 'CurriculumInventory');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $rootNode->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation',
            'http://ns.medbiq.org/curriculuminventory/v1/ curriculuminventory.xsd');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:lom', 'http://ltsc.ieee.org/xsd/LOM');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:a', 'http://ns.medbiq.org/address/v1/');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cf', 'http://ns.medbiq.org/competencyframework/v1/');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:co', 'http://ns.medbiq.org/competencyobject/v1/');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:hx', 'http://ns.medbiq.org/lom/extend/v1/');
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:m', 'http://ns.medbiq.org/member/v1/');
        $dom->appendChild($rootNode);
        //
        // ReportID
        //
        $reportIdNode = $dom->createElement('ReportID', $inventory['report']['id']);
        $reportIdNode->setAttribute('domain', "idd:{$inventory['report']['domain']}:cireport");
        $rootNode->appendChild($reportIdNode);

        // Institution
        $institutionNode = $dom->createElementNS('http://ns.medbiq.org/member/v1/', 'm:Institution');
        $rootNode->appendChild($institutionNode);
        $institutionNameNode = $dom->createElementNS('http://ns.medbiq.org/member/v1/', 'm:InstitutionName');
        $institutionNameNode->appendChild($dom->createTextNode($inventory['institution']->name));
        $institutionNode->appendChild($institutionNameNode);
        $institutionIdNode = $dom->createElementNS('http://ns.medbiq.org/member/v1/', 'm:InstitutionID', $inventory['institution']->aamc_code);
        $institutionIdNode->setAttribute('domain', 'idd:aamc.org:institution');
        $institutionNode->appendChild($institutionIdNode);
        $addressNode = $dom->createElementNS('http://ns.medbiq.org/member/v1/', 'm:Address');
        $institutionNode->appendChild($addressNode);
        $streetAddressNode = $dom->createElementNS('http://ns.medbiq.org/address/v1/', 'a:StreetAddressName');
        $streetAddressNode->appendChild($dom->createTextNode($inventory['institution']->address_street));
        $addressNode->appendChild($streetAddressNode);
        $cityNode = $dom->createElementNS('http://ns.medbiq.org/address/v1/', 'a:City', $inventory['institution']->address_city);
        $addressNode->appendChild($cityNode);
        $stateNode = $dom->createElementNS('http://ns.medbiq.org/address/v1/', 'a:StateOrProvince', $inventory['institution']->address_state_or_province);
        $addressNode->appendChild($stateNode);
        $zipcodeNode = $dom->createElementNS('http://ns.medbiq.org/address/v1/', 'a:PostalCode', $inventory['institution']->address_zipcode);
        $addressNode->appendChild($zipcodeNode);
        $countryNode = $dom->createElementNS('http://ns.medbiq.org/address/v1/', 'a:Country');
        $addressNode->appendChild($countryNode);
        $countryCodeNode = $dom->createElementNS('http://ns.medbiq.org/address/v1/', 'a:CountryCode', $inventory['institution']->address_country_code);
        $countryNode->appendChild($countryCodeNode);
        //
        // Program
        //
        $programNode = $dom->createElement('Program');
        $rootNode->appendChild($programNode);
        $programNameNode = $dom->createElement('ProgramName');
        $programNameNode->appendChild($dom->createTextNode($inventory['program']->title));
        $programNode->appendChild($programNameNode);
        $programIdNode = $dom->createElement('ProgramID', $inventory['program']->program_id);
        $programIdNode->setAttribute('domain', "idd:{$inventory['report']['domain']}:program");
        $programNode->appendChild($programIdNode);

        //
        // various other report attributes
        //
        $titleNode = $dom->createElement('Title');
        $titleNode->appendChild($dom->createTextNode($inventory['report']['name']));
        $rootNode->appendChild($titleNode);
        $reportDateNode = $dom->createElement('ReportDate', date('Y-m-d'));
        $rootNode->appendChild($reportDateNode);
        $reportingStartDateNode = $dom->createElement('ReportingStartDate', $inventory['report']['start_date']);
        $rootNode->appendChild($reportingStartDateNode);
        $reportingEndDateNode = $dom->createElement('ReportingEndDate', $inventory['report']['end_date']);
        $rootNode->appendChild($reportingEndDateNode);
        $languageNode = $dom->createElement('Language', 'en-US'); // @todo make this configurable
        $rootNode->appendChild($languageNode);
        $descriptionNode = $dom->createElement('Description');
        $descriptionNode->appendChild($dom->createTextNode($inventory['report']['description']));
        $rootNode->appendChild($descriptionNode);
        // default supporting link url to the site url of this Ilios instance.
        if (array_key_exists('supporting_link', $inventory['report'])) {
            $supportingLinkNode = $dom->createElement('SupportingLink', $inventory['report']['supporting_link']);
            $rootNode->appendChild($supportingLinkNode);
        }
        //
        // Events
        //
        $eventsNode = $dom->createElement('Events');
        $rootNode->appendChild($eventsNode);
        foreach ($inventory['events'] as $event) {
            $eventNode = $dom->createElement('Event');
            $eventsNode->appendChild($eventNode);
            $eventNode->setAttribute('id', 'E' . $event['session_id']);
            $eventTitleNode = $dom->createElement('Title');
            $eventNode->appendChild($eventTitleNode);
            $eventTitleNode->appendChild($dom->createTextNode($event['title']));
            $eventDurationNode = $dom->createElement('EventDuration', 'PT' . $event['duration'] . 'M');
            $eventNode->appendChild($eventDurationNode);
            if ('' !== trim($event['description'])) {
                $descriptionNode = $dom->createElement('Description');
                $eventNode->appendChild($descriptionNode);
                $descriptionNode->appendChild($dom->createTextNode(trim(strip_tags($event['description']))));
            }
            if (array_key_exists('keywords', $event)) {
                foreach ($event['keywords'] as $keyword) {
                    $keywordNode = $dom->createElement('Keyword');
                    $eventNode->appendChild($keywordNode);
                    $keywordNode->setAttribute('hx:source', 'MeSH');
                    $keywordNode->setAttribute('hx:id', $keyword['mesh_descriptor_uid']);
                    $descriptorNode = $dom->createElementNS('hx', 'string');
                    $keywordNode->appendChild($descriptorNode);
                    $descriptorNode->appendChild($dom->createTextNode($keyword['name']));
                }
            }
            if ($event['is_assessment_method']) {
                $assessmentMethodNode = $dom->createElement('AssessmentMethod');
                $eventNode->appendChild($assessmentMethodNode);
                // from the spec:
                // AssessmentMethod has the following attribute
                //
                // purpose
                // Indicates whether the assessment is used for formative or
                // summative assessment. Use of the purpose attribute is required.
                // Valid values are Formative and Summative.
                //
                // @todo Ilios does not have this info. Map this somehow.
                $assessmentMethodNode->setAttribute('purpose', 'Summative');
                $assessmentMethodNode->appendChild($dom->createTextNode($event['method_title']));
            } else {
                $instructionalMethodNode = $dom->createElement('InstructionalMethod');
                $eventNode->appendChild($instructionalMethodNode);
                $instructionalMethodNode->setAttribute('primary', 'true');
                $instructionalMethodNode->appendChild($dom->createTextNode($event['method_title']));
            }
        }

        //
        // Expectations
        //
        $expectationsNode = $dom->createElement('Expectations');
        $rootNode->appendChild($expectationsNode);
        //
        // Academic Levels
        //
        $academicLevelsNode = $dom->createElement('AcademicLevels');
        $rootNode->appendChild($academicLevelsNode);
        $levelsInProgramNode = $dom->createElement('LevelsInProgram', count($inventory['academic_levels']));
        $academicLevelsNode->appendChild($levelsInProgramNode);
        foreach ($inventory['academic_levels'] as $level) {
            $levelNode = $dom->createElement('Level');
            $academicLevelsNode->appendChild($levelNode);
            $levelNode->setAttribute('number', $level['level']);
            $labelNode = $dom->createElement('Label');
            $levelNode->appendChild($labelNode);
            $labelNode->appendChild($dom->createTextNode($level['name']));
            if ('' !== trim($level['description'])) {
                $descriptionNode = $dom->createElement('Description');
                $levelNode->appendChild($descriptionNode);
                $descriptionNode->appendChild($dom->createTextNode($level['description']));
            }
        }
        //
        // Sequence
        //
        $sequenceNode = $dom->createElement('Sequence');
        $rootNode->appendChild($sequenceNode);
        if ('' !== trim($inventory['sequence']->description)) {
            $sequenceDescriptionNode = $dom->createElement('Description');
            $sequenceNode->appendChild($sequenceDescriptionNode);
            $sequenceDescriptionNode->appendChild($dom->createTextNode($inventory['sequence']->description));
        }
        foreach ($inventory['sequence_blocks'] as $block) {
            $this->_createSequenceBlockXml($dom, $sequenceNode, $block);
        }
        //
        // Integration
        //
        $integrationNode = $dom->createElement('Integration');
        $rootNode->appendChild($integrationNode);

        return $dom;
    }

    /**
     * Recursively creates and appends sequence block nodes to the XML document
     * @param DomDocument $dom the document object
     * @param DomElement $parentNode the node object to append to
     * @param array $block the current sequence block
     */
    protected function _createSequenceBlockXml (DomDocument $dom, DomElement $parentNode, array $block)
    {
        $sequenceBlockNode = $dom->createElement('SequenceBlock');
        $parentNode->appendChild($sequenceBlockNode);
        $sequenceBlockNode->setAttribute('id', $block['sequence_block_id']);
        switch ($block['status']) {
            case Curriculum_Inventory_Sequence_Block::OPTIONAL :
                $sequenceBlockNode->setAttribute('required', 'Optional');
                break;
            case Curriculum_Inventory_Sequence_Block::REQUIRED :
                $sequenceBlockNode->setAttribute('required', 'Required');
                break;
            case Curriculum_Inventory_Sequence_Block::REQUIRED_IN_TRACK :
                $sequenceBlockNode->setAttribute('required', 'Required in Track');
                break;
            default :
                // SOL!
                // @todo handle this. e.g. throw an exception.
        }
        switch ($block['child_sequence_order']) {
            case Curriculum_Inventory_Sequence_Block::ORDERED :
                $sequenceBlockNode->setAttribute('order', 'Optional');
                break;
            case Curriculum_Inventory_Sequence_Block::UNORDERED :
                $sequenceBlockNode->setAttribute('order', 'Required');
                break;
            case Curriculum_Inventory_Sequence_Block::PARALLEL :
                $sequenceBlockNode->setAttribute('order', 'Required in Track');
                break;
            default :
                // @todo handle this. e.g. throw an exception.
        }

        $sequenceBlockNode->setAttribute('minimum', $block['minimum']);
        $sequenceBlockNode->setAttribute('maximum', $block['maximum']);

        if ($block['track']) {
            $sequenceBlockNode->setAttribute('track', 'true');
        } else {
            $sequenceBlockNode->setAttribute('track', 'false');
        }

        $titleNode = $dom->createElement('Title');
        $sequenceBlockNode->appendChild($titleNode);
        $titleNode->appendChild($dom->createTextNode($block['title']));

        if ('' !== trim($block['description'])) {
            $descriptionNode = $dom->createElement('Description');
            $sequenceBlockNode->appendChild($descriptionNode);
            $descriptionNode->appendChild($dom->createTextNode($block['description']));
        }

        // currently, only start/end-date are supported for <Timing>
        // @todo add duration
        $timingNode = $dom->createElement('Timing');
        $sequenceBlockNode->appendChild($timingNode);
        $datesNode = $dom->createElement('Dates');
        $timingNode->appendChild($datesNode);
        $startDateNode = $dom->createElement('StartDate', $block['start_date']);
        $datesNode->appendChild($startDateNode);
        $endDateNode = $dom->createElement('EndDate', $block['end_date']);
        $datesNode->appendChild($endDateNode);

        // academic level
        $levelNode = $dom->createElement('Level', "/CurriculumInventory/AcademicLevels/Level[@number='{$block['academic_level_number']}']");
        $sequenceBlockNode->appendChild($levelNode);

        // clerkship type
        if (array_key_exists('clerkship_model', $block)) {
            $clerkshipModelNode = $dom->createElement('ClerkshipModel', $block['clerkship_model']);
            $sequenceBlockNode->appendChild($clerkshipModelNode);
        }

        // event references
        if (array_key_exists('event_references', $block)) {
            foreach ($block['event_references'] as $reference) {
                $sequenceBlockEventNode = $dom->createElement('SequenceBlockEvent');
                $sequenceBlockNode->appendChild($sequenceBlockEventNode);
                if ($reference['required']) {
                    $sequenceBlockEventNode->setAttribute('required', 'true');
                } else {
                    $sequenceBlockEventNode->setAttribute('required', 'false');
                }
                $refUri = "/CurriculumInventory/Events/Event[@id='E{$reference['session_id']}']}";
                $eventReferenceNode = $dom->createElement('EventReferenceNode', $refUri);
                $sequenceBlockEventNode->appendChild($eventReferenceNode);
                // @todo add start/end-date
            }
        }

        // recursively generate XML for nested sequence blocks
        if (array_key_exists('children', $block)) {
            foreach ($block['children'] as $child) {
                $this->_createSequenceBlockXml($dom, $sequenceBlockNode, $child);
            }
        }

    }

    /**
     * Retrieves curriculum inventory report data.
     * @param int $reportId the report id
     * @return array an associated array, containing the inventory. Data is keyed off by:
     *     'report' ... an array holding various report-related properties, such as id, domain etc
     *     'program' ... an object representing the program associated with the report
     *     'institution' ... an object representing the curriculum inventory's owning institution
     *     'events'
     *     'expectations'
     *     'academic_levels'
     *     'sequence'  ... the inventory sequence object
     *     'sequence_blocks'
     *     'integration'
     * @throws Ilios_Exception
     */
    protected function _loadCurriculumInventory ($reportId)
    {
        //
        // load inventory from various sources
        //
        $invReport = $this->invReport->getRowForPrimaryKeyId($reportId);
        if (! isset($invReport)) {
            throw new Ilios_Exception('Could not load the report for the given id ( ' . $reportId . ')');
        }

        $program = $this->program->getRowForPrimaryKeyId($invReport->program_id);
        if (! isset($program)) {
            throw new Ilios_Exception('Could not load program for program id ( ' . $program->program_id . ')');
        }

        $invInstitution  = $this->invInstitution->getRowForPrimaryKeyId($program->owning_school_id);
        if (! isset($invInstitution)) {
            throw new Ilios_Exception('Could not load curriculum institution for school id ( ' . $program->owning_school_id . ')');
        }
        $invSequence = $this->invSequence->getRowForPrimaryKeyId($reportId);
        if (! isset($invSequence)) {
            throw new Ilios_Exception('Could not load curriculum sequence for report id ( ' . $reportId . ')');
        }

        $events = $this->inventory->getEvents($reportId);
        $keywords = $this->inventory->getEventKeywords($reportId);
        $levels = $this->invAcademicLevel->getAppliedLevels($reportId);
        $sequenceBlocks = $this->invSequenceBlock->getBlocks($reportId);
        $eventReferences = $this->inventory->getEventReferences($reportId);

        // report (and some program) properties
        $report = array();
        $report['id'] = $invReport->year . '-' . $program->program_id . '-' . time(); // report id format: "<academic year>-<program id>-<current timestamp>"
        $report['domain'] = $this->config->item('curriculum_inventory_institution_domain');
        $report['date'] = date('Y-m-d');
        $report['name'] = $invReport->name;
        $report['description'] = $invReport->description;
        $report['start_date'] = $invReport->start_date;
        $report['end_date'] = $invReport->end_date;

        $supportingLink = $this->config->item('curriculum_inventory_supporting_link');
        if ($supportingLink) {
            $report['supporting_link'] = $supportingLink;
        }

        //
        // transmogrify inventory data for reporting and fill in the blanks
        //

        // add keywords to event
        $events = $this->_addKeywordsToEvents($events, $keywords);

        $sequenceBlocks = $this->_prepareBlockEventsForOutput($sequenceBlocks, $eventReferences);
        // transform sequence blocks from a flat list into a nested structure
        $sequenceBlocks = $this->_buildSequenceBlockHierarchy($sequenceBlocks);

        //
        // aggregate inventory into single return-array
        //
        $rhett = array();
        $rhett['report'] = $report;
        $rhett['program'] = $program;
        $rhett['institution'] = $invInstitution;
        $rhett['sequence'] = $invSequence;
        $rhett['sequence_blocks'] = $sequenceBlocks;
        $rhett['events'] = $events;
        $rhett['academic_levels'] = $levels;
        return $rhett;
    }

    /**
     * @param array $events
     * @param array $keywords
     * @return array
     */
    protected function _addKeywordsToEvents (array $events, array $keywords)
    {
        foreach ($keywords as $keyword) {
            $eventId = $keyword['session_id'];
            if (! array_key_exists($eventId, $events)) {
                continue;
            }
            if (! array_key_exists('keywords', $events[$eventId])) {
                $events[$eventId]['keywords'] = array();
            }
            $events[$eventId]['keywords'][] = $keyword;
        }
        return $events;
    }

    /**
     * Iterate over a list of given sequence blocks and link to events, massage data
     * @param array $sequenceBlocks
     * @param array $eventReferences
     * @return array
     */
    protected function _prepareBlockEventsForOutput (array $sequenceBlocks, array $eventReferences)
    {
        for ($i = 0, $n = count($sequenceBlocks); $i < $n; $i++) {
            // link to events
            $courseId = $sequenceBlocks[$i]['course_id'];
            if ($courseId && array_key_exists($courseId, $eventReferences)) {
                $sequenceBlocks[$i]['event_references'] = $eventReferences[$courseId];
            } else {
                $sequenceBlocks[$i]['event_references'] = array();
            }
            // map course clerkship type to "Clerkship Model"
            // @todo review business rules
            switch ($sequenceBlocks[$i]['course_clerkship_type_id']) {
                case Course_Clerkship_Type::INTEGRATED :
                    $sequenceBlocks[$i]['clerkship_model'] = 'integrated';
                    break;
                case Course_Clerkship_Type::BLOCK :
                case Course_Clerkship_Type::LONGITUDINAL :
                    $sequenceBlocks[$i]['clerkship_model'] = 'rotation';
                    break;
                default :
                    // do nothing
            }
        }
        return $sequenceBlocks;
    }

    /**
     *
     * @param array $sequenceBlocks a list of sequence blocks
     * @param int|null $parentBlockId the id of the parent block
     * @return array the nests
     */
    protected function _buildSequenceBlockHierarchy (array $sequenceBlocks, $parentBlockId = null)
    {

        $rhett = array();
        $remainder = array();
        for ($i = 0, $n = count($sequenceBlocks); $i < $n; $i++) {
            $block = $sequenceBlocks[$i];
            if ($parentBlockId === $block['parent_sequence_block_id']) {
                $rhett[] = $block;
            } else {
                $remainder[] = $block;
            }
        }
        for ($i = 0, $n = count($rhett); $i < $n; $i++) {
            // recursion!
            $children = $this->_buildSequenceBlockHierarchy ($remainder, $rhett[$i]['sequence_block_id']);
            if (count($children)) {
                // sort children if the sort order demands it
                if (Curriculum_Inventory_Sequence_Block::ORDERED === $rhett[$i]['child_sequence_order']) {
                    usort($children, array($this, '_sortSequenceBlocks'));
                }
                $rhett[$i]['children'] = $children;
            }
        }
        return $rhett;
    }

    /**
     * Comparison function for sorting sequence block arrays.
     * @param array $a associative array representing a sequence block
     * @param array $b associative array representing a sequence block
     * @return int
     * @see usort()
     * @see Curriculum_Inventory_Manager::_buildSequenceBlockHierarchy()
     */
    protected function _sortSequenceBlocks (array $a, array $b)
    {
        if ($a['order_in_sequence'] === $b['order_in_sequence']) {
            return 0;
        }
        return ($a['order_in_sequence'] > $b['order_in_sequence']) ? 1 : -1;
    }
}
