<?php
/**
 * @author QuangChien(Glorious Victory) <quangchien01.it@gmail.com>
 * @copyright Copyright © 2022 QuangChien(Glorious Victory) <https://www.facebook.com/quangchien01>. All rights reserved.
 */

namespace Victory\Blog\Model\Config\Source;

/**
 * Category Tree
 *
 */
class CategoryPath extends CategoryTree
{
    protected function _getOptions($itemId = 0)
    {
        $childs =  $this->_getChilds();
        $options = [];

        if (!$itemId) {
            $options[] = [
                'label' => '',
                'value' => 0,
            ];
        }

        if (isset($childs[$itemId])) {
            foreach ($childs[$itemId] as $item) {
                $data = [
                    'label' => $item->getTitle() .
                        ($item->getIsActive() ? '' : ' ('.__('Disabled').')'),
                    'value' => ($item->getParentIds() ? $item->getPath().'/' : '') . $item->getId(),
                ];
                if (isset($childs[$item->getId()])) {
                    $data['optgroup'] = $this->_getOptions($item->getId());
                }

                $options[] = $data;
            }
        }

        return $options;
    }
}
