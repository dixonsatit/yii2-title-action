<?php

namespace dixonstarter\grid;

use Yii;
use Closure;
use yii\helpers\Html;
use yii\helpers\Url;
use  yii\grid\DataColumn;

/**
 * Data action columns (DataColumn + ActionColumn)
 * @author Sathit Seethaphon <dixonsatit@gmail.com>
 */
class TitleActionColumn extends DataColumn
{

  public $headerOptions = ['class' => 'action-column'];

/**
     * @var string the template used for composing each cell in the action column.
     * Tokens enclosed within curly brackets are treated as controller action IDs (also called *button names*
     * in the context of action column). They will be replaced by the corresponding button rendering callbacks
     * specified in [[buttons]]. For example, the token `{view}` will be replaced by the result of
     * the callback `buttons['view']`. If a callback cannot be found, the token will be replaced with an empty string.
     *
     * As an example, to only have the view, and update button you can add the ActionColumn to your GridView columns as follows:
     *
     * ```php
     * ['class' => 'yii\grid\ActionColumn', 'template' => '{view} {update}'],
     * ```
     * buttonGroup Template "<p class="title-action btn-group btn-group-xs text-center" role="group"> {view}  {update}  {delete} </p>"
     * default Template "<p class="title-action"> {view} {update} {delete} </p>"
     * @see buttons
     */
  public $template = null;

  public $buttons = [];

  public $controller;

  public $visibleButtons = [];

  public $urlCreator;

  public $buttonOptions = [];

  public $linkStyle = 'buttongroup'; //default,buttongroup

  public $labelStyle = 'iconText'; //icon,text,iconText

  public $attribute;

  private $templateStyle;

  public function init()
  {
      parent::init();
      $this->renderLinkStyle();
      $this->initDefaultButtons();
  }

  public function renderLinkStyle(){
    if($this->template===null){
      if($this->linkStyle==='buttongroup'){
          $this->buttonOptions=['class'=>'btn btn-default'];
          $this->template = '<p class="title-action btn-group btn-group-xs text-center" role="group"> {view}  {update}  {delete} </p>';
      }else{
        if($this->labelStyle=='text'){
          $this->template = '<p class="title-action"> {view} | {update} | {delete} </p>';
        }else{
          $this->template = '<p class="title-action"> {view} {update} {delete} </p>';
        }

      }
    }
  }

  public function createUrl($action, $model, $key, $index)
  {
      if (is_callable($this->urlCreator)) {
          return call_user_func($this->urlCreator, $action, $model, $key, $index);
      } else {
          $params = is_array($key) ? $key : ['id' => (string) $key];
          $params[0] = $this->controller ? $this->controller . '/' . $action : $action;
          return Url::toRoute($params);
      }
  }

  protected function initDefaultButtons()
  {
      if (!isset($this->buttons['view'])) {
          $this->buttons['view'] = function ($url, $model, $key) {
              $options = array_merge([
                  'title' => Yii::t('yii', 'View'),
                  'aria-label' => Yii::t('yii', 'View'),
                  'data-pjax' => '0',
              ], $this->buttonOptions);
              return Html::a($this->renderLinkLabel('<span class="glyphicon glyphicon-eye-open"></span> ',Yii::t('yii', 'View')), $url, $options);
          };
      }
      if (!isset($this->buttons['update'])) {
          $this->buttons['update'] = function ($url, $model, $key) {
              $options = array_merge([
                  'title' => Yii::t('yii', 'Update'),
                  'aria-label' => Yii::t('yii', 'Update'),
                  'data-pjax' => '0',
              ], $this->buttonOptions);
              return Html::a($this->renderLinkLabel('<span class="glyphicon glyphicon-pencil"></span> ',Yii::t('yii', 'Update')), $url, $options);
          };
      }
      if (!isset($this->buttons['delete'])) {
          $this->buttons['delete'] = function ($url, $model, $key) {
              $options = array_merge([
                  'title' => Yii::t('yii', 'Delete'),
                  'aria-label' => Yii::t('yii', 'Delete'),
                  'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                  'data-method' => 'post',
                  'data-pjax' => '0',
              ], $this->buttonOptions);
              return Html::a($this->renderLinkLabel('<span class="glyphicon glyphicon-trash"></span> ',Yii::t('yii', 'Delete')), $url, $options);
          };
      }
  }

  public function renderLinkLabel($icon,$label){
    if($this->labelStyle=='iconText'){
      return $icon.$label;
    }elseif($this->labelStyle=='icon'){
      return $icon;
    }else{
      return $label;
    }
  }

  protected function renderLinkAction($model, $key, $index)
  {
      return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
          $name = $matches[1];
          if (isset($this->visibleButtons[$name])) {
              $isVisible = $this->visibleButtons[$name] instanceof \Closure
                  ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                  : $this->visibleButtons[$name];
          } else {
              $isVisible = true;
          }
          if ($isVisible && isset($this->buttons[$name])) {
              $url = $this->createUrl($name, $model, $key, $index);
              return call_user_func($this->buttons[$name], $url, $model, $key);
          } else {
              return '';
          }
      }, $this->template);
  }

  protected function renderDataCellContent($model, $key, $index)
  {
      $link = $this->renderLinkAction($model, $key, $index);

      if ($this->content === null) {
          $data =  $this->grid->formatter->format($this->getDataCellValue($model, $key, $index), $this->format);
      } else {
          $data =  parent::renderDataCellContent($model, $key, $index);
      }

      return $data.$link;
  }
}
