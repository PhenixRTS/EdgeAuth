from setuptools import setup

setup(
    name='phenix-edge-auth',
    version='0.1.0',
    description='A example Python package',
    url='https://github.com/shuds13/pyexample',
    author='Brandon Drake',
    author_email='tomoguisuru@gmail.com',
    license='Apache-2.0',
    packages=['edgeauth'],
    install_requires=[
        'argparse',
        'PrettyPrinter',
    ],

    classifiers=[
        'Development Status :: 1 - Planning',
        'Intended Audience :: Business',
        'License :: OSI Approved :: BSD License',
        'Operating System :: POSIX :: Linux',
        'Programming Language :: Python :: 3.6',
    ],
)
