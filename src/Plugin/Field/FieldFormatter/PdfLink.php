<?php

namespace Drupal\pdf\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * @FieldFormatter(
 *  id = "pdf_link",
 *  label = @Translation("PDF: Link to view in PDF.js"),
 *  description = @Translation("Create a link to an address, where the PDF gets shown in PDF.js."),
 *  field_types = {"file"}
 * )
 */
class PdfLink extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $config = \Drupal::config('pdf.settings');
    $viewer_path = $config->get('custom_viewer') ? $config->get('custom_viewer') : base_path() . 'libraries/pdf.js/web/viewer.html';
    $keep_pdfjs = $this->getSetting('keep_pdfjs');
    $extra_options = array_filter(array_intersect_key($this->getSettings(), array_flip([
      'page',
      'zoom',
      'custom_zoom',
      'pagemode',
    ])));
    if (isset($extra_options['zoom']) && $extra_options['zoom'] == 'custom') {
      $extra_options['zoom'] = $extra_options['custom_zoom'];
    }
    unset($extra_options['custom_zoom']);
    $query = UrlHelper::buildQuery($extra_options);
    foreach ($items as $delta => $item) {
      if ($item->entity->getMimeType() == 'application/pdf') {
        $file_url = file_create_url($item->entity->getFileUri());
        $src = file_create_url($viewer_path) . '?file=' . rawurlencode($file_url);
        $src = !empty($query) && $keep_pdfjs ? $src . '#' . $query : $src;
        $html = [
          '#markup' => "<a href='" . htmlspecialchars($src) . "'>Link</a>",
          '#allowed_tags' => ['a']
        ];
        $elements[$delta] = $html;
      }
      else {
        $elements[$delta] = [
          '#theme' => 'file_link',
          '#file' => $item->entity,
        ];
      }
    }
    return $elements;
  }

}
