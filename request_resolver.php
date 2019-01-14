<?php

/**
 * @return string
 */
function getDataFromSearchRequest(): string
{
    if (isset($_POST['wordToSearch']))
    {
        return $_POST['wordToSearch'];
    } else {
        return '';
    }

}

$wordToSrc = getDataFromSearchRequest();
if ($wordToSrc != '')
{
    $indexing->searchAction($wordToSrc);
}
