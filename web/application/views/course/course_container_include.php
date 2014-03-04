<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$formPrefix = '<form id="course_form" method="POST" action="' . current_url() . '/doesntGetUsed" onsubmit="return false;">';

$addNewEntityLink = '<button id="add_new_course" class="small secondary radius button" onClick="ilios.cm.displayAddNewCourseDialog();">' . $add_course_string . '</button>';

$searchNewEntityLink = '<button class="small radius button" onclick="ilios.cm.cs.displayCourseSearchPanel();">' . $word_search_string . '</button>';

$entityContainerHeader =<<< EOL
<li class="title">
    <span class="data-type">Course Title</span>
    <span class="data" id="summary-course-title"></span>
</li>
<li class="course-id">
    <span class="data-type">Course ID</span>
    <span class="data" id="summary-course-id"></span>
</li>
<li class="course-year">
    <span class="data-type">Course Year</span>
    <span class="data" id="summary-course-year"></span>
</li>
<li class="course-level">
    <span class="data-type">Course Level</span>
    <span class="data" id="summary-course-level"></span>
</li>
EOL;
$entityContainerContent =<<< EOL
<div class="row">
    <div class="column label">
        <label class="entity_widget_title" for="course_title">{$phrase_course_name_string}</label>
    </div>
    <div class="column data">
        <input type="text" id="course_title" name="course_title" value="" disabled="disabled" size="50" />
    </div>
    <div class="column actions">
        <a href=""
           class="tiny white radius button"
           onclick="ilios.course_summary.showCourseSummary(ilios.cm.currentCourseModel); return false;"
           id="show_course_summary_link">{$phrase_show_course_summary}</a>
    </div>
</div>

<div class="row">
    <div class="column label">
        <label for="external_course_id">{$external_course_id_string}</label>
    </div>
    <div class="column data">
        <input type="text" id="external_course_id" value="" />
        <input type="text" readonly="readonly" id="course_unique_id" class="readonly-text note" value="" />
    </div>
    <div class="column actions"></div>
</div>

<div class="row">
    <div class="column label">
        <label>{$phrase_course_year_string}</label>
    </div>
    <div class="column data">
        <span id="course_year_start" class="read_only_data"></span>
    </div>
</div>

<div class="row">
    <div class="column label">
        <label for="course_level_selector">{$phrase_course_level_string}</label>
    </div>
    <div class="column data">
        <select id="course_level_selector" name="course_level">
            <option value="1" selected="selected">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="column label">
        <label for="clerkship_type_selector">{$phrase_clerkship_type_string}</label>
    </div>
    <div class="column data">
        <select id="clerkship_type_selector">
            <option value="">{$phrase_not_a_clerkship_string}</option>
EOL;

foreach ($clerkship_types as $clerkshipTypeId => $clerkshipTypeTitle) {
    $entityContainerContent .= '<option value="' . $clerkshipTypeId . '">'. htmlentities($clerkshipTypeTitle) . '</option>';
}

$entityContainerContent .=<<< EOL
        </select>
    </div>
</div>
<div class="row">
    <div class="column label">
        <label>{$phrase_program_cohort_string}</label>
    </div>
    <div class="column data">
        <div id="cohort_level_table_div"></div>
    </div>
    <div class="column actions">
        <a href=""
           class="tiny radius button"
           onclick="ilios.ui.onIliosEvent.fire({action: 'gen_dialog_open', event: 'find_cohort_and_program'}); return false;"
           id="select_cohorts_link">{$select_cohorts_string}</a>
    </div>
</div>

<div class="row">
    <div class="column label">
        <label>{$phrase_associated_learners_string}</label>
    </div>
    <div class="column data">
        <div id="course_associated_learners" class="read_only_data scroll_list"></div>
    </div>
</div>

<div class="row">
    <div class="column label">
        <label for="start_date_calendar_button">{$phrase_start_date_string}</label>
    </div>
    <div class="column data">
        <span id="course_start_date" class="read_only_data">No Date Selected</span>
        <span id="start_date_calendar_button" class="calendar_button"></span>
    </div>
    <div class="column actions"></div>
</div>

<div class="row">
    <div class="column label">
        <label for="end_date_calendar_button">{$phrase_end_date_string}</label>
    </div>
    <div class="column data">
        <span id="course_end_date" class="read_only_data">No Date Selected</span>
        <span id="end_date_calendar_button" class="calendar_button"></span>
    </div>
    <div class="column actions"></div>
</div>

<div class="row">
    <div class="column label">
        <label for="">{$word_competencies_string}</label>
    </div>
    <div class="column data">
        <div id="-1_competency_picker_selected_text_list" class="read_only_data scroll_list"></div>
    </div>
    <div class="column actions"></div>
</div>

<div class="row">
    <div class="column label">
        <label for="">{$word_disciplines_string}</label>
    </div>
    <div class="column data">
        <div id="-1_discipline_picker_selected_text_list" class="read_only_data scroll_list"></div>
    </div>
    <div class="column actions">
        <a href=""
           class="tiny radius button"
           onclick="ilios.ui.onIliosEvent.fire({action: 'default_dialog_open', event: 'discipline_picker_show_dialog', container_number: -1}); return false;"
           id="disciplines_search_link">{$word_search_string}</a>
    </div>
</div>

<div class="row">
    <div class="column label">
        <label for="">{$word_directors_string}</label>
    </div>
    <div class="column data">
        <div id="-1_director_picker_selected_text_list" class="read_only_data scroll_list"></div>
    </div>
    <div class="column actions">
        <a href="" class="tiny radius button"
           onclick="ilios.ui.onIliosEvent.fire({action: 'default_dialog_open', event: 'director_picker_show_dialog', container_number: -1}); return false;"
           id="directors_search_link">{$word_search_string}</a>
    </div>
</div>

<div class="row">
    <div class="column label">
        <label for="">{$phrase_mesh_terms_string}</label>
    </div>
    <div class="column data">
        <div id="-1_mesh_terms_picker_selected_text_list" class="read_only_data scroll_list"></div>
    </div>
    <div class="column actions">
        <a href="" class="tiny radius button"
           onclick="ilios.cm.displayMeSHDialogForCourse(); return false;"
           id="mesh_search_link">{$word_search_string}</a>
    </div>
</div>

<div class="row">
    <div class="column label">
        <div class="collapsed_widget" id="-1_learning_material_expand_widget"></div>
        <label for="">{$phrase_learning_materials_string}</label>
        <span id="-1_learning_material_count" style="margin-right: 9px;"> (0)</span>
    </div>
    <div class="column data">
        <div class="scroll_list" style="display: none;">
            <ul class="learning_material_list" id="-1_learning_material_list"></ul>
        </div>
    </div>
    <div class="column actions">
        <a href="" class="tiny radius button"
           onclick="ilios.ui.onIliosEvent.fire({action: 'alm_dialog_open', container_number: -1}); return false;"
           id="course_learning_material_search_link">{$word_search_string} / {$word_add_string}</a>
    </div>
</div>

<div class="row">
    <div class="column label">
        <div class="collapsed_widget" id="-1_objectives_container_expand_widget"></div>
        <label id="-1_objectives_container_label" for="">{$phrase_learning_objectives_string} (0)</label>
    </div>
    <div class="column data">
        <div id="-1_objectives_container" style="display: none;"></div>
    </div>
    <div class="column actions">
        <a href="" class="tiny radius button"
           onclick="ilios.cm.addNewCourseObjective(-1); return false;"
           id="add_objective_link">{$add_objective_string}</a>
    </div>
</div>
EOL;

$addNewSomethingId = '';
$addNewSomethingAction = '';
$addNewSomethingDisplayText = '';

$suffixingContent = '<div id="course_session_header_div"><div id="sessions_summary"></div>';

$progressDivStyleDefinition = 'position: absolute; left: 38%;';

// KLUDGE!
// see if a course is about to be loaded in the page by checking if a stub object has been passed to the view.
// if so then display a "loading course details" status progress message in the sessions_summary element.
// @see GitHub Issue #203
// [ST 2013/10/18]
if (-1 === $course_id) {
    $suffixingContent .= generateProgressDivMarkup($progressDivStyleDefinition . ' display: none', ''); // hide it.
} else {
    $suffixingContent .= generateProgressDivMarkup($progressDivStyleDefinition,
       t('general.phrases.loading_all_course_sessions'));
}

$suffixingContent .= '
    <div id="course_sessions_toolbar" class="collapse_children_toggle_link hidden">
        <select id="session_ordering_selector" onchange="ilios.cm.session.reorderSessionDivs(); return false;">
            <option selected="selected">' . $sort_alpha_asc . '</option>
            <option>' . $sort_alpha_desc . '</option>
            <option>' . $sort_date_asc . '</option>
            <option>' . $sort_date_desc . '</option>
        </select>

        <button class="small secondary radius button"
                onclick="ilios.cm.session.collapseOrExpandSessions(false); return false;"
                id="expand_sessions_link">' . $collapse_sessions_string . '</button>
    </div>

    <div style="clear: both;"></div>
</div>

<div id="session_container"></div>

<div class="add_primary_child_link">
    <button class="small secondary radius button" onclick="ilios.cm.session.userRequestsSessionAddition();"
                id="add_new_session_link" disabled="disabled">' . $add_session_string . '</button>
</div>';

$saveDraftAction = 'ilios.cm.transaction.saveCourseAsDraft();';
$publishAction = 'ilios.cm.transaction.performCoursePublish();';
$revertAction = 'ilios.cm.transaction.revertChanges();';

$publishAllString = t('general.phrases.publish_all');
$publishNowString = '<div id="-1_publish_warning" class="yellow_warning_icon" '
                        . 'style="display: none;"></div>'
                        . t('general.phrases.publish_course');

createContentContainerMarkup($formPrefix, $addNewEntityLink, $searchNewEntityLink, $entityContainerHeader,
    $entityContainerContent, $addNewSomethingId, $addNewSomethingAction, $addNewSomethingDisplayText, $suffixingContent,
    $saveDraftAction, $publishAction, $revertAction, true, true, true, true,
    t('general.phrases.save_all_draft'),
    t('general.phrases.save_draft'), $publishAllString, $publishNowString,
    t('general.phrases.reset_form'), true, true);
?>
