<?php

/**
 * @file
 * Definition of Drupal\ds\Tests\LayoutClassesTest.
 */

namespace Drupal\ds\Tests;

/**
 * Test managing of layouts and CSS classes
 */
class LayoutClassesTest extends BaseTest {

  /**
   * Implements getInfo().
   */
  public static function getInfo() {
    return array(
      'name' => t('Layouts'),
      'description' => t('Tests for managing layouts and classes on Field UI screen.'),
      'group' => t('Display Suite'),
    );
  }

  /**
   * Test selecting layouts, classes, region to block and fields.
   */
  function testDStestLayouts() {

    // Check that the ds_3col_equal_width layout is not available (through the alter).
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertNoRaw('ds_3col_stacked_equal_width', 'ds_3col_stacked_equal_width not available');

    // Create code, preprocess block field.
    $this->dsCreateCodeField();
    $this->dsCreateBlockField();
    $this->dsCreatePreprocessField();

    $layout = array(
      'additional_settings[layout]' => 'ds_2col_stacked',
    );

    $assert = array(
      'regions' => array(
        'header' => '<td colspan="8">' . t('Header') . '</td>',
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
        'footer' => '<td colspan="8">' . t('Footer') . '</td>',
      ),
    );

    $fields = array(
      'fields[post_date][region]' => 'header',
      'fields[author][region]' => 'left',
      'fields[links][region]' => 'left',
      'fields[body][region]' => 'right',
      'fields[comments][region]' => 'footer',
      'fields[test_field][region]' => 'left',
      'fields[test_block_field][region]' => 'left',
      'fields[submitted][region]' => 'left',
      'fields[ds_extras_extra_test_field][region]' => 'header',
    );

    // Setup first layout.
    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureClasses();
    $this->dsSelectClasses();
    $this->dsConfigureUI($fields);

    // Assert the two extra fields are found.
    $this->drupalGet('admin/structure/types/manage/article/display');
    $this->assertRaw('ds_extras_extra_test_field');
    $this->assertRaw('ds_extras_second_field');

    // Assert we have some configuration in our database.
    $count = count(config_get_storage_names_with_prefix('ds.layout_settings.node.article.default'));
    $this->assertEqual($count, 1, t('1 record found for layout settings for node article'));

    // Lookup settings and verify.
    $data = config('ds.layout_settings.node.article.default')->get('settings');
    $this->assertTrue(in_array('ds_extras_extra_test_field', $data['regions']['header']), t('Extra field is in header'));
    $this->assertTrue(in_array('post_date', $data['regions']['header']), t('Post date is in header'));
    $this->assertTrue(in_array('test_field', $data['regions']['left']), t('Test field is in left'));
    $this->assertTrue(in_array('author', $data['regions']['left']), t('Author is in left'));
    $this->assertTrue(in_array('links', $data['regions']['left']), t('Links is in left'));
    $this->assertTrue(in_array('test_block_field', $data['regions']['left']), t('Test block field is in left'));
    $this->assertTrue(in_array('submitted', $data['regions']['left']), t('Submitted field is in left'));
    $this->assertTrue(in_array('body', $data['regions']['right']), t('Body is in right'));
    $this->assertTrue(in_array('comments', $data['regions']['footer']), t('Comments is in footer'));
    $this->assertTrue(in_array('class_name_1', $data['classes']['header']), t('Class name 1 is in header'));
    $this->assertTrue(empty($data['classes']['left']), t('Left has no classes'));
    $this->assertTrue(empty($data['classes']['right']), t('Right has classes'));
    $this->assertTrue(in_array('class_name_2', $data['classes']['footer']), t('Class name 2 is in header'));

    // Create a article node and verify settings.
    $settings = array(
      'type' => 'article',
    );
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->nid);

    // Assert regions.
    $this->assertRaw('group-header', 'Template found (region header)');
    $this->assertRaw('group-header class_name_1', 'Class found (class_name_1)');
    $this->assertRaw('group-left', 'Template found (region left)');
    $this->assertRaw('group-right', 'Template found (region right)');
    $this->assertRaw('group-footer', 'Template found (region footer)');
    $this->assertRaw('group-footer class_name_2', 'Class found (class_name_2)');

    // Assert custom fields.
    $this->assertRaw('field-name-test-field', t('Custom field found'));
    $this->assertRaw('Test field', t('Custom field found'));
    $this->assertRaw('field-name-test-block-field', t('Custom block field found'));
    $this->assertRaw('Recent content</h2>', t('Custom block field found'));
    $this->assertRaw('Submitted by', t('Submitted field found'));
    $this->assertText('This is an extra field made available through "Extra fields" functionality.');

    // Test HTML5 wrappers
    $this->assertNoRaw('<header', 'Header not found.');
    $this->assertNoRaw('<footer', 'Footer not found.');
    $this->assertNoRaw('<article', 'Article not found.');
    $wrappers = array(
      'additional_settings[region_wrapper][header]' => 'header',
      'additional_settings[region_wrapper][right]' => 'footer',
      'additional_settings[region_wrapper][layout_wrapper]' => 'article',
    );
    $this->dsConfigureUI($wrappers);
    $this->drupalGet('node/' . $node->nid);
    $this->assertRaw('<header', 'Header found.');
    $this->assertRaw('<footer', 'Footer found.');
    $this->assertRaw('<article', 'Article found.');

    // Let's create a block field, enable the full mode first.
    $edit = array('additional_settings[modes][view_modes_custom][full]' => '1');
    $this->drupalPost('admin/structure/types/manage/article/display', $edit, t('Save'));

    // Select layout.
    $layout = array(
      'additional_settings[layout]' => 'ds_2col',
    );

    $assert = array(
      'regions' => array(
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
      ),
    );
    $this->dsSelectLayout($layout, $assert, 'admin/structure/types/manage/article/display/full');

    // Create new block field.
    $edit = array(
      'additional_settings[region_to_block][new_block_region]' => 'Block region',
      'additional_settings[region_to_block][new_block_region_key]' => 'block_region',
    );
    $this->drupalPost('admin/structure/types/manage/article/display/full', $edit, t('Save'));
    $this->assertRaw('<td colspan="8">' . t('Block region') . '</td>', 'Block region found');

    // Configure fields
    $fields = array(
      'fields[author][region]' => 'left',
      'fields[links][region]' => 'left',
      'fields[body][region]' => 'right',
      'fields[ds_test_field][region]' => 'block_region',
    );
    $this->dsConfigureUI($fields, 'admin/structure/types/manage/article/display/full');

    // Set block in sidebar
    $edit = array(
      'blocks[ds_extras_block_region][region]' => 'sidebar_first',
    );
    $this->drupalPost('admin/structure/block', $edit, t('Save blocks'));

    // Assert the block is on the node page.
    $this->drupalGet('node/' . $node->nid);
    $this->assertRaw('Block region</h2>', 'Block region found');
    $this->assertText('Test code field on node ' . $node->nid, 'Post date in block');

    // Change layout via admin/structure/ds/layout-change.
    // First verify that header and footer are not here.
    $this->drupalGet('admin/structure/types/manage/article/display/full');
    $this->assertNoRaw('<td colspan="8">' . t('Header') . '</td>', 'Header region not found');
    $this->assertNoRaw('<td colspan="8">' . t('Footer') . '</td>', 'Footer region not found');

    // Remap the regions.
    $edit = array(
      'ds_left' => 'header',
      'ds_right' => 'footer',
      'ds_block_region' => 'footer',
    );
    $this->drupalPost('admin/structure/ds/change-layout/node/article/full/ds_2col_stacked', $edit, t('Save'), array('query' => array('destination' => 'admin/structure/types/manage/article/display/full')));

    // Verify new regions.
    $this->assertRaw('<td colspan="8">' . t('Header') . '</td>', 'Header region found');
    $this->assertRaw('<td colspan="8">' . t('Footer') . '</td>', 'Footer region found');
    $this->assertRaw('<td colspan="8">' . t('Block region') . '</td>', 'Block region found');

    // Verify settings.
    $data = config('ds.layout_settings.node.article.full')->get('settings');
    $this->assertTrue(in_array('author', $data['regions']['header']), t('Author is in header'));
    $this->assertTrue(in_array('links', $data['regions']['header']), t('Links field is in header'));
    $this->assertTrue(in_array('body', $data['regions']['footer']), t('Body field is in footer'));
    $this->assertTrue(in_array('ds_test_field', $data['regions']['footer']), t('Test field is in footer'));

    // Test that a default view mode with no layout is not affected by a disabled view mode.
    $edit = array(
      'additional_settings[layout]' => '',
      'additional_settings[modes][view_modes_custom][full]' => FALSE,
    );
    $this->drupalPost('admin/structure/types/manage/article/display', $edit, t('Save'));
    $this->drupalGet('node/' . $node->nid);
    $this->assertNoText('Test code field on node 1', 'No ds field from full view mode layout');
  }
}