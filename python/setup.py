from setuptools import setup, find_packages

with open("README.md", "r") as fh:
    long_description = fh.read()

requirements = [
    # 'argparse',
    # 'PrettyPrinter',
]

setup_requirements = [
    'pytest-runner',
]

test_requirements = [
    'pytest>=3',
]

setup(
    name='phenix-edge-auth',
    version='1.2.5',
    description='Easily generate secure digest tokens to use with the Phenix platform',
    long_description=long_description,
    long_description_content_type="text/markdown",
    url='https://github.com/PhenixRTS/EdgeAuth/python',
    author='Tim Gronkowski',
    author_email='tim.gronkowski@phenixrts.com',
    license='Apache-2.0',
    packages=find_packages(include=['edgeauth', 'edgeauth.*']),
    install_requires=requirements,
    setup_requires=setup_requirements,
    test_suite='tests',
    tests_require=test_requirements,
    classifiers=[
        "Programming Language :: Python :: 3",
        "License :: OSI Approved :: Apache Software License",
        "Operating System :: OS Independent",
    ],
    entry_points={
        'console_scripts': [
            'edgeauth=edgeauth.cli:main',
        ],
    },
    python_requires='>=3.6',
)
