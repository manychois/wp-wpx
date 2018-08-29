<?php
namespace Manychois\Wpx;

/**
 * Holds all necessary information to render a search form.
 */
class SearchForm
{
    /**
     * Method of the search form used by WordPress.
     */
    const METHOD = 'get';
    /**
     * The name attribute value of the search field used by WordPress.
     */
    const INPUT_NAME = 's';

    /**
     * The action attribute value of the search form.
     * @var string
     */
    public $action;
    /**
     * The unescaped search term.
     * @var string
     */
    public $query = '';
}