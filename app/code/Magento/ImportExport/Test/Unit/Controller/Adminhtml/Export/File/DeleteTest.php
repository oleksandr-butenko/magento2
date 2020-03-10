<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Export\File;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileSystem;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

    /**
     * @var \Magento\ImportExport\Controller\Adminhtml\Export\File\Delete|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deleteController;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directory;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileSystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->file = $this->getMockBuilder(\Magento\Framework\Filesystem\DriverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getResultRedirectFactory', 'getMessageManager']
        );

        $this->redirect = $this->createPartialMock(\Magento\Backend\Model\View\Result\Redirect::class, ['setPath']);

        $this->resultRedirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->redirect);
        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);


        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->deleteController = $this->objectManagerHelper->getObject(
            Delete::class,
            [
                'context' => $this->context,
                'filesystem' => $this->fileSystem,
                'file' => $this->file
            ]
        );
    }

    /**
     * Tests download controller with different file names in request.
     */
    public function testExecuteSuccess()
    {
        $this->request->method('getParam')
            ->with('filename')
            ->willReturn('sampleFile');

        $this->fileSystem->expects($this->once())->method('getDirectoryRead')->will($this->returnValue($this->directory));
        $this->directory->expects($this->once())->method('isFile')->willReturn(true);
        $this->file->expects($this->once())->method('deleteFile')->willReturn(true);
        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->deleteController->execute();
    }

    /**
     * Tests download controller with different file names in request.

     */
    public function testExecuteFileDoesntExists()
    {
        $this->request->method('getParam')
            ->with('filename')
            ->willReturn('sampleFile');

        $this->fileSystem->expects($this->once())->method('getDirectoryRead')->will($this->returnValue($this->directory));
        $this->directory->expects($this->once())->method('isFile')->willReturn(false);
        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $this->deleteController->execute();
    }

    /**
     * Test execute() with invalid file name
     * @param string $requestFilename
     * @dataProvider executeDataProvider
     */
    public function testExecuteInvalidFileName($requestFilename)
    {
        $this->request->method('getParam')->with('filename')->willReturn($requestFilename);
        $this->messageManager->expects($this->once())->method('addErrorMessage');

        $this->deleteController->execute();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'Relative file name' => ['../.htaccess'],
            'Empty file name' => [''],
            'Null file name' => [null],
        ];
    }
}
