<?php

namespace Navigation\Navigation;

class Node {
    
    /**
     * Css class name
     * 
     * @var string
     */
    protected $class;
    
    /**
     * Node name
     * 
     * @var string 
     */
    protected $name;
    
    /**
     * Css id name
     * 
     * @var string 
     */
    protected $id;

    /**
     * Controller name
     * 
     * @var string 
     */
    protected $controller;
    
    /**
     * Action name
     * 
     * @var string 
     */
    protected $action;
    
    /**
     * Url name
     * 
     * @var url
     */
    protected $url;
    
    /**
     * Node's childs
     * 
     * @var array 
     */
    public $childs;
    
    /**
     * target html element
     * 
     * @var string
     */
    protected $target;
    
    /**
     * Parents node
     * 
     * @var \Navigation\Navigation\Node
     */
    protected $parent;

	/**
	 * Class Link
	 *
	 * @var \Navigation\Navigation\Node
	 */
	protected $classLink;

	/**
	 * Onclick JS
	 *
	 * @var \Navigation\Navigation\Node
	 */
	protected $click;

	/**
	 * Onclick JS
	 *
	 * @var \Navigation\Navigation\Node
	 */
	protected $open;

	/**
	 * Onclick JS
	 *
	 * @var \Navigation\Navigation\Node
	 */
	protected $close;

	/**
	 * Menu wrapper type div, ul
	 *
	 * @var \Navigation\Navigation\Node
	 */
	protected $wrapper;
    
    /**
     * isActive node flag
     * 
     * @var bool 
     */
    protected $isActive = false;

    /**
     * Get css class name
     * 
     * @return string
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * Get node name
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Get css id name
     * 
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * get Url
     * 
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Get node childs
     * 
     * @return array | null
     */
    public function getChilds() {
        return $this->childs;
    }

	/**
	 * Get node childs
	 *
	 * @return array | null
	 */
	public function getClassLink() {
		return $this->classLink;
	}

	/**
	 * Get node childs
	 *
	 * @return array | null
	 */
	public function getWrapper() {
		return $this->wrapper;
	}

	/**
	 * Get JS
	 */
	public function getClick() {
		return $this->click;
	}

	/**
	 */
	public function getOpen() {
		return $this->open;
	}

	/**
	 * Get JS
	 */
	public function getClose() {
		return $this->close;
	}

    /**
     * Get html target
     * 
     * @return string
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * Get parents node
     * 
     * @return \null
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Get controller name
     * 
     * @return string
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Get action name
     * 
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set css class name
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setClass($value) {
        $this->class = $value;

        return $this;
    }

    /**
     * Set css id name
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setId($value) {
        $this->id = $value;

        return $this;
    }

	/**
	 * Set css link
	 *
	 * @param type $value
	 * @return \Navigation\Navigation\Node
	 */
	public function setClassLink($value) {
		$this->classLink = $value;

		return $this;
	}

	/**
	 * Set wrapper
	 *
	 * @param type $value
	 * @return \Navigation\Navigation\Node
	 */
	public function setWrapper($value) {
		$this->wrapper = $value;
		return $this;
	}

	/**
	 * Set onclick JS
	 *
	 * @param type $value
	 * @return \Navigation\Navigation\Node
	 */
	public function setClick($value) {
		$this->click = $value;
		return $this;
	}

	/**
	 * Set onclick JS
	 *
	 * @param type $value
	 * @return \Navigation\Navigation\Node
	 */
	public function setOpen($value) {
		$this->open = $value;
		return $this;
	}

	/**
	 * Set onclick JS
	 *
	 * @param type $value
	 * @return \Navigation\Navigation\Node
	 */
	public function setClose($value) {
		$this->close = $value;
		return $this;
	}

    /**
     * Set node name
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setName($value) {
        $this->name = $value;

        return $this;
    }

	/**
	 * Set childs
	 *
	 * @param type $value
	 * @return \Navigation\Navigation\Node
	 */
	public function setData($value) {
		$this->childs = $value;

		return $this;
	}

    /**
     * Set url
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setUrl($value) {
        $this->url = $value;

        return $this;
    }

    /**
     * Set childs
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setChilds($value) {
        $this->childs = $value;

        return $this;
    }

    /**
     * Set html target
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setTarget($value) {
        $this->target = $value;

        return $this;
    }

    /**
     * Set Parent
     * 
     * @param \Navigation\Navigation\Node $value
     * @return \Navigation\Navigation\Node
     */
    public function setParent($value) {
        $this->parent = $value;

        return $this;
    }

    /**
     * Set controller name
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setController($value) {
        $this->controller = $value;

        return $this;
    }

    /**
     * Set action name
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setAction($value) {
        $this->action = $value;

        return $this;
    }
    
    /**
     * Set active flag
     * 
     * @param type $value
     * @return \Navigation\Navigation\Node
     */
    public function setActive($value) {
        $this->isActive = $value;

        return $this;
    }

    /**
     * Is node active?
     * 
     * @return bool
     */
    public function isActive() {
        return $this->isActive;
    }

    /**
     * Has node any childs
     * 
     * @return bool
     */
    public function hasChilds() {
        return 0 < count($this->getChilds());
    }

}
