<?php
/**
 * User: balint
 * Date: 2016. 02. 29.
 * Time: 10:21
 */

namespace Evista\Perform\ValueObject;

use Evista\Perform\Exception\FormFieldException;
use Evista\Perform\Exception\CantMoveToDestination;
use Evista\Perform\Exception\NoFileUploadedException;

class UploadedFile
{
    private $userFileName = false;
    private $safeName = false;
    private $type = false;
    private $tmpName = false;
    private $error = false;
    private $size = false;
    private $realType = false;
    private $userExtension = false;
    private $uploadDir = '';


    public function __construct($name, $index, array $_files, $uploadDir)
    {
        if (! $_files[$name]['name'][$index]) {
            throw new NoFileUploadedException('Emtpy file');
        }

        $this->uploadDir = $uploadDir;
        $this->userFileName = $_files[$name]['name'][$index];
        $this->safeName = md5($this->userFileName + microtime(true));
        $this->type = $_files[$name]['type'][$index];
        $this->tmpName = $_files[$name]['tmp_name'][$index];
        $this->error = $_files[$name]['error'][$index];
        $this->size = $_files[$name]['size'][$index];
        $this->findRealType()->getPathInfo();
        $this->userExtension = $this->pathInfo['extension'];
    }

    public static function create($fieldName, $_files, $uploadDir)
    {

        if (count($_files) < 1) {
            throw new NoFileUploadedException('Emtpy file');
        }
        // $fieldName includes a '[]' when the upload is multiple
        $fieldName = str_replace(['[', ']'], '', $fieldName);

        if (! array_key_exists($fieldName, $_files)) {
            throw FormFieldException::noSuchFieldName($fieldName);
        }

        $requestedFileField = $_files[$fieldName];
        // Count the uploaded files via one of it's child
        if (! array_key_exists('name', $requestedFileField)) {
            throw new NoNameParam('No name param found in file details');
        }
        $uploadedCount = count($requestedFileField['name']);

        for ($index = 0; $index <= $uploadedCount - 1; $index++) {
            yield new self($fieldName, $index, $_files, $uploadDir);
        }
    }

    /**
     * Move file to destination
     * @param  [type] $destination [description]
     * @return $this [type]              [description]
     * @throws CantMoveToDestination
     */
    public function moveToDestination($destination = false)
    {
        if (! $destination) {
          $destination = $this->uploadDir;
        }

        $fileName = $this->getSafeName();
        if (strpos($this->getRealType(), $this->getUserExtension) == 0) {
            $fileName = $this->getSafeName() . '.' . $this->getUserExtension();
        }
        
        try {
            rename($this->tmpName, $destination . '/' . $fileName);
        } catch (\Exception $exception) {
            throw new CantMoveToDestination("Error Processing Request", 1);
        }

        return $this;
    }

    /**
     * adds real file type
     * @return [type] [description]
     */
    public function findRealType()
    {
        $finfo = new \finfo();
        // Move file to the upload folder for checking
        $fileName = md5(microtime());
        $destination = $this->uploadDir;
        move_uploaded_file($this->tmpName, $destination.'/'.$fileName);
        $this->realType = $finfo->file($destination.'/'.$fileName, FILEINFO_MIME_TYPE);
        $this->tmpName = $destination.'/'.$fileName;


        return $this;
    }

    /**
     * adds pathinfo result to a local variable
     * @return [type] [description]
     */
    public function getPathInfo()
    {
        $this->pathInfo = pathinfo($this->userFileName);

        return $this;
    }

    /**
     * Get the value of User: balint
     *
     * @return mixed
     */
    public function getUserFileName()
    {
        return $this->userFileName;
    }

    /**
     * Set the value of User: balint
     *
     * @param mixed name
     *
     * @return self
     */
    public function setUserFileName($name)
    {
        $this->userFileName = $name;

        return $this;
    }

    /**
     * Get the value of Type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of Type
     *
     * @param mixed type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of Tmp Name
     *
     * @return mixed
     */
    public function getTmpName()
    {
        return $this->tmpName;
    }

    /**
     * Set the value of Tmp Name
     *
     * @param mixed tmpName
     *
     * @return self
     */
    public function setTmpName($tmpName)
    {
        $this->tmpName = $tmpName;

        return $this;
    }

    /**
     * Get the value of Error
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the value of Error
     *
     * @param mixed error
     *
     * @return self
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get the value of Size
     *
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the value of Size
     *
     * @param mixed size
     *
     * @return self
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get the value of User Extension
     *
     * @return mixed
     */
    public function getUserExtension()
    {
        return $this->userExtension;
    }

    /**
     * Set the value of User Extension
     *
     * @param mixed userExtension
     *
     * @return self
     */
    public function setUserExtension($userExtension)
    {
        $this->userExtension = $userExtension;

        return $this;
    }

    /**
     * @return bool|string
     */
    public function getSafeName()
    {
        return $this->safeName;
    }

    /**
     * @param bool|string $safeName
     * @return UploadedFile
     */
    public function setSafeName($safeName)
    {
        $this->safeName = $safeName;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getRealType()
    {
        return $this->realType;
    }

    /**
     * @param boolean $realType
     * @return UploadedFile
     */
    public function setRealType($realType)
    {
        $this->realType = $realType;

        return $this;
    }

    /**
     * Get the value of Upload Dir
     *
     * @return mixed
     */
    public function getUploadDir()
    {
        return $this->uploadDir;
    }

    /**
     * Set the value of Upload Dir
     *
     * @param mixed uploadDir
     *
     * @return self
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;

        return $this;
    }

}
