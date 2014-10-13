.. _filesystem:

Filesystem Abstraction
======================

The ``rampage\\filesystem`` component provides an OO abstraction layer for
filesystem access.

A filesystem might be a local, remote or even a virtual filesystem.

.. toctree::
    :hidden:
    filesystem/localfilesystem
    filesystem/writablelocalfilesystem


.. _filesystem.fsinterface:

rampage\\filesystem\\FilesystemInterface
----------------------------------------

This interface defines basic filesystem access via the :term:`ArrayAccess` and :term:`RecursiveInterator` patterns.
It encapsulates the underlying filesystem and provides container like access. Much like `Phar`_ or `ZipArchive`_

It is not intended to provide write access.

The following classes are provided as implementation for this interface:

    * :doc:`filesystem.LocalFilesystem` for fs access on disk.


.. _filesystem.rw.fsinterface:

rampage\\filesystem\\WritableFilesystemInterface
------------------------------------------------

This interface enhances the :ref:`FilesystemInterface <filesystem.fsinterface>` by defining write capabilities which are:

    * adding files
    * deleting files
    * creating directories
    * updating the access/modified timestamps (touch)

An implementation must provide these methods and respond gracefully, when they're not supported (i.e. creating directories).

The following classes are provided as implementation for this interface:

    * :doc:`filesystem.WritableLocalFilesystem` for fs access on disk.


.. _Phar: http://php.net/manual/de/class.phar.php
.. _ZipArchive: http://de1.php.net/manual/de/class.ziparchive.php
