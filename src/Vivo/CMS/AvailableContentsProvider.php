<?php
namespace Vivo\CMS;

/**
 * Class is responsible for giving document
 */
class AvailableContentsProvider
{

    protected $config;

    protected $contents = array();

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Returns content classes, labels and options available for given document.
     * @param \Vivo\CMS\Model\Document|\Vivo\CMS\Model\Folder $document
     * @param string $documentPath
     * @return array
     */
    public function getAvailableContents(Model\Folder $document, $documentPath)
    {
        $documentPath   = strtolower($documentPath);
        $this->contents = $this->config['available_contents'];
        $restrictions = $this->config['restrictions'];

        if (isset($restrictions['document_type'][get_class($document)])){
            $this->intersect($restrictions['document_type'][get_class($document)]);
        }

        $role = 'some_user_role'; //TODO get user role
        if (isset($restrictions['user_role'][$role])){
            $this->intersect($restrictions['user_role'][$role]);
        }

        if (isset($restrictions['site'])){
            $this->intersect($restrictions['site']);
        }
        if (isset($restrictions['document_path']) && is_array($restrictions['document_path'])){
            foreach ($restrictions['document_path'] as $path => $contents) {
                if(strpos($documentPath, strtolower($path)) !== false) {
                    $this->intersect($contents);
                }
            }
        }
        return $this->contents;
    }

    /**
     * 'Intersects' current contents with restrictions
     * @param array $restriction
     */
    protected function intersect(array $restriction)
    {
        foreach ($this->contents as $key => $contentsConfig) {
            if (!in_array($key, $restriction)) {
                unset($this->contents[$key]);
            }
        }
    }
}
