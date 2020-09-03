from setuptools import setup

with open("README.md", "r") as fh:
    long_description = fh.read()

setup(
    name='phenix-edge-auth',
    version='0.1.0',
    description='A example Python package',
    long_description=long_description,
    long_description_content_type="text/markdown",
    url='https://github.com/tomoguisuru/EdgeAuth/python',
    author='Brandon Drake',
    author_email='tomoguisuru@gmail.com',
    license='Apache-2.0',
    packages=['edgeauth'],
    install_requires=[
        'argparse',
        'PrettyPrinter',
    ],

    classifiers=[
        "Programming Language :: Python :: 3",
        "License :: OSI Approved :: MIT License",
        "Operating System :: OS Independent",
    ],
    python_requires='>=3.6',
)
